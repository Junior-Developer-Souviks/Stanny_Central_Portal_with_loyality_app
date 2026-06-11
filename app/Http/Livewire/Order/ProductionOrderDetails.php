<?php

namespace App\Http\Livewire\Order;
use App\Models\Order;
use \App\Models\Product;
use \App\Models\Invoice;
use \App\Models\StockFabric;
use \App\Models\StockProduct;
use \App\Models\OrderStockEntry;
use \App\Models\ChangeLog;
use \App\Models\Delivery;
use \App\Models\OrderItem;
use \App\Models\Fabric;
use \App\Models\Measurement;
use Livewire\Component;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductionOrderDetails extends Component
{
    public $showModal = false;
    public $selectedItem = [];
    public $orderItems = [];
    public $rows = [];
    public $orderId;
    public $latestOrders = [];
    public $order;
    public $available_meter;
    public $selectedDeliveryItem = [];
    public $actualUsage = [];
    // public $deliveryType = 'full';
    public $showExtraStockPrompt;
    public $fabrics = [];
    public $stockEntries = [];
    public $deliveryEntries = [];
    public $fabricSearch = [];
    public $searchResults = [];

    public function mount($id){
        $this->orderId = $id;
       $this->order = Order::with(['items','files'])->findOrFail($this->orderId);
        // dd($this->order);
        $invoicePayment = Invoice::where('order_id', $this->order->id)->orderBy('id','desc')->first(); 
        if($invoicePayment){
            $this->order->total_amount = $invoicePayment->net_price;
            $this->order->paid_amount = $invoicePayment->net_price - $invoicePayment->required_payment_amount;
            $this->order->remaining_amount = $invoicePayment->required_payment_amount;
        }

        
         // Fetch the latest 5 orders for the user (customer)
         $this->latestOrders = Order::where('customer_id',$this->order->customer_id)
                                     ->latest()
                                     ->where('id', '!=', $this->order->id)
                                     ->take(5)
                                     ->get();

        // Load OrderStockEntry and populate stockEntries + fabricSearch
        $existingEntries = OrderStockEntry::where('order_id', $this->order->id)->get();

        foreach ($existingEntries as $index => $entry) {
            $fabric = Fabric::find($entry->fabric_id);

            $this->stockEntries[$index] = [
                'order_item_id'     => $entry->order_item_id,
                'fabric_id'         => $entry->fabric_id,
                'quantity'          => $entry->quantity,
                'available_value'   => $entry->available_value,
            ];

            $this->fabricSearch[$index] = $fabric ? $fabric->title : 'Unknown Fabric';
        }
        
    }


   


// New Code
public function updateStock($itemId)
{
    $item = collect($this->orderItems)->firstWhere('id', $itemId);

    $orderItemId = $item['id'];
    $collectionId = $item['collection_id'];
    $unitType = $item['stock_entry_data']['type'];
    $adminId = auth()->guard('admin')->user()->id;

    $rules = [];
    $messages = [];

    foreach ($this->stockEntries as $entryIndex => $entry) {
        $rowKey = "required_meter_$entryIndex";

        $rules["rows.$rowKey"] = ['required', 'numeric', 'min:0.01'];
        $messages["rows.$rowKey.required"] = 'Required meter is mandatory';
        $messages["rows.$rowKey.numeric"] = 'Must be a valid number';
        $messages["rows.$rowKey.min"] = 'Must be at least 0.01';

        if ($entryIndex > 0 && $collectionId == 1) {
            $rules["stockEntries.$entryIndex.fabric_id"] = ['required'];
            $messages["stockEntries.$entryIndex.fabric_id.required"] = 'Fabric selection is required';
        }
    }

    $this->validate($rules, $messages);

    try {
        DB::beginTransaction();

        foreach ($this->stockEntries as $entryIndex => $entry) {
            $rowKey = "required_meter_$entryIndex";
            $enteredQuantity = (float)($this->rows[$rowKey] ?? 0);

            $fabricId = $collectionId == 1
                ? ($entryIndex === 0 ? ($item['fabrics']['id'] ?? null) : ($entry['fabric_id'] ?? null))
                : null;

            $productId = $collectionId == 2
                ? ($item['product']['id'] ?? null)
                : null;

            $stock = null;
            $availableStock = 0;

            if ($collectionId == 1 && $fabricId) {
                $stock = StockFabric::firstOrNew(['fabric_id' => $fabricId]);
                $availableStock = $stock->qty_in_meter ?? 0;
            } elseif ($collectionId == 2 && $productId) {
                $stock = StockProduct::firstOrNew(['product_id' => $productId]);
                $availableStock = $stock->qty_in_pieces ?? 0;
            }

            $previousEntry = OrderStockEntry::where([
                'order_item_id' => $orderItemId,
                'fabric_id' => $fabricId,
                'product_id' => $productId,
            ])->first();

            $previousQuantity = $previousEntry ? $previousEntry->quantity : 0;
            $maxAllowed = $availableStock + $previousQuantity;

            if ($enteredQuantity > $maxAllowed) {
                $this->addError("rows.$rowKey", "Exceeds available stock. Max allowed: {$maxAllowed} {$unitType}");
                DB::rollBack();
                return;
            }

            $stockEntryData = [
                'order_id' => $this->orderId,
                'order_item_id' => $orderItemId,
                'fabric_id' => $fabricId,
                'product_id' => $productId,
                'quantity' => $enteredQuantity,
                'unit' => $unitType,
                'created_by' => $adminId,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            if ($previousEntry) {
                $previousEntry->update($stockEntryData);
            } else {
                OrderStockEntry::create($stockEntryData);
            }

            $difference = $enteredQuantity - $previousQuantity;
            if ($stock) {
                if ($collectionId == 1) {
                    $stock->qty_in_meter -= $difference;
                } elseif ($collectionId == 2) {
                    $stock->qty_in_pieces -= $difference;
                }
                $stock->save();
            }

            ChangeLog::create([
                'done_by' => $adminId,
                'purpose' => 'stock_entry_update',
                'data_details' => json_encode([
                    'order_item_id' => $orderItemId,
                    'fabric_id' => $fabricId,
                    'product_id' => $productId,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $enteredQuantity,
                    'difference' => $difference
                ]),
            ]);
        }

        DB::commit();
        $this->dispatch('stock-updated-successfully', message: 'Stock updated successfully!');
        $this->loadOrderItems();
        $this->openStockModal($orderItemId);
        return redirect()->route('production.order.details', $this->orderId);

    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        $this->dispatch('error', message: 'Error updating stock: ' . $e->getMessage());
    }
}





// New
public function revertBackStock($itemId, $inputName, $entryId)
{
    try {
        DB::beginTransaction();

        $item = collect($this->orderItems)->firstWhere('id', $itemId);

        if (!$item) {
            throw new \Exception("Order item not found for ID: $itemId");
        }
        if (!$entryId) {
            throw new \Exception("Invalid stock entry ID.");
        }

        // $stockEntry = OrderStockEntry::findOrFail($entryId);
        $stockEntry = OrderStockEntry::where('id', $entryId)
            ->where('order_item_id', $itemId)
            ->first();
        
        if (!$stockEntry) {
            throw new \Exception("Stock entry not found or already reverted.");
        }

        // Revert only the exact stock
        if ($item['collection_id'] == 1 && $stockEntry->fabric_id) {
            $stock = StockFabric::where('fabric_id', $stockEntry->fabric_id)->first();
            if ($stock) {
                $stock->increment('qty_in_meter', $stockEntry->quantity);
            }
        } elseif ($item['collection_id'] == 2 && $stockEntry->product_id) {
            $stock = StockProduct::where('product_id', $stockEntry->product_id)->first();
            if ($stock) {
                $stock->increment('qty_in_pieces', $stockEntry->quantity);
            }
        }

        $stockEntry->delete();

        DB::commit();

        $this->loadOrderItems();
        $this->openStockModal($itemId);
        return redirect()->route('production.order.details', $this->orderId);

    } catch (\Throwable $e) {
        dd($e->getMessage());
        DB::rollBack();
        session()->flash('error', 'Error reverting stock: ' . $e->getMessage());
    }
}




   

    // New
    public function loadOrderItems()
{
    $this->orderItems = $this->order->items
        ->filter(function ($item) {
            return $item->status === 'Process' &&
                   $item->tl_status === 'Approved' &&
                   $item->admin_status === 'Approved' &&
                   $item->assigned_team === 'production';
        })
        ->map(function ($item) {
            $product = Product::find($item->product_id);

            $stockData = Helper::getStockEntryData(
                $item->collection,
                $item->fabrics,
                $item->product_id,
                $this->orderId,
                $item->id
            );

            $hasStockEntry = OrderStockEntry::where('order_item_id', $item->id)->exists();

            // Get changelogs for this item
            $logs = Changelog::whereJsonContains('data_details->order_item_id', $item->id)
                ->whereIn('purpose', ['stock_entry_update', 'extra_stock_entry', 'delivery_proceed'])
                ->get();

            $latestDeliveryLog = $logs->where('purpose', 'delivery_proceed')->last();
            $deliveryCount = 0;

           $logTooltip = $logs->map(function ($log) use ($item, &$deliveryCount,$latestDeliveryLog) {
            $details = json_decode($log->data_details, true);
            // Find the latest delivery_proceed log (if any)
            return match ($log->purpose) {
                // For product-based collections (collection_id = 2)
                'stock_entry_update' => $item->collection == 2
                    ? 'Entered Quantity: ' . ($details['entered_quantity'] ?? '-')
                    : null,

                'extra_stock_entry' => $item->collection == 2
                    ? 'Extra Quantity: ' . ($details['extra_quantity'] ?? '-')
                    : null,

                'delivery_proceed' => match (true) {
                    // For fabric-based collections (collection_id = 1)
                    $item->collection == 1 && isset($details['delivered_fabrics']) && $log->id === $latestDeliveryLog->id => collect($details['delivered_fabrics'])->map(function ($fabric) {
                        $name = $fabric['fabric'] ?? 'Unknown';
                        // $entered = $fabric['entered_quantity'] ?? '-';
                        $delivered = $fabric['delivered_quantity'] ?? '-';
                        $extra_quantity = $fabric['extra_meter'] ?? '-';
                        return "$name:extra_meter $extra_quantity , delivered quantity $delivered";
                    })->implode(' | '),

                    // For product-based collections (collection_id = 2)
                    $item->collection == 2 && isset($details['delivered_quantity']) => match (++$deliveryCount) {
                        1 => '1st Delivered Quantity : ' . $details['delivered_quantity'],
                        2 => '2nd Delivered Quantity : ' . $details['delivered_quantity'],
                        3 => '3rd Delivered Quantity : ' . $details['delivered_quantity'],
                        default => "{$deliveryCount}th Delivered Quantity : " . $details['delivered_quantity']
                    },

                    default => null,
                },

                default => null,
            };
        })->filter()->implode(' | ');


            $stock = null;
            $totalStock = 0;
            $used = 0;
            $deliveredQty = 0;
            $remainingQty = 0;
            $isDelivered = false;

              $fabricId = $item->fabric->id ?? null;
            if ($item->collection == 1 && $fabricId) {
                // Fabric collection
                $stock = StockFabric::where('fabric_id', $fabricId)->first();
                $totalStock = $stock?->qty_in_meter ?? 0;
                $used = OrderStockEntry::where('order_item_id', $item->id)->sum('quantity');
                $isDelivered = Delivery::where('order_item_id', $item->id)
                    ->where('fabric_id', $fabricId)
                    ->exists();

            } elseif ($item->collection == 2) {
                // Product collection
                $stock = StockProduct::where('product_id', $item->product_id)->first();
                $totalStock = $stock?->qty_in_pieces ?? 0;
                $used = OrderStockEntry::where('order_item_id', $item->id)->sum('quantity');
                $isDelivered = Delivery::where('order_item_id', $item->id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                $deliveredQty = Delivery::where('order_item_id', $item->id)->sum('delivered_quantity');
                $remainingQty = $item->quantity - $deliveredQty;
            }

            $initialStock = $totalStock + $used;
            $totalUsed = $initialStock - $totalStock;

            $extra = \App\Helpers\Helper::ExtraRequiredMeasurement($item->product_name);
            $measurements = Measurement::where('product_id', $item->product_id)
                ->orderBy('position','ASC')
                ->get()
                ->map(function ($measurement) use ($item) {
                    $selected = $item->measurements->firstWhere('measurement_name', $measurement->title);
                    return [
                        'measurement_name'          => $measurement->title,
                        'measurement_title_prefix'  => $measurement->short_code,
                        'measurement_value'         => $selected ? $selected->measurement_value : '',
                        'measurement_remarks'         => $selected ? $selected->remarks : '',
                    ];
                });
            return [
                'id' => $item->id,
                'product_name' => $item->product_name ?? $product?->name,
                'collection_id' => $item->collection,
                'collection_title' => $item->collectionType?->title ?? "",
                'fabrics' => $item->fabric,
                'product' => $item->product,
                'measurements' => $measurements,
                'catalogue' => $item->catalogue_id ? $item->catalogue : "",
                'catalogue_id' => $item->catalogue_id,
                'cat_page_number' => $item->cat_page_number,
                'cat_page_item' => $item->cat_page_item,
                'price' => $item->piece_price,
                'quantity' => $item->quantity,
                'product_image' => $product?->product_image,
                'stock_entry_data' => $stockData,
                'has_stock_entry' => $hasStockEntry,
                'total_used' => $totalUsed,
                'initial_stock' => $initialStock,
                'is_delivered' => $isDelivered,
                'delivered_quantity' => $deliveredQty,
                'remaining_to_deliver' => $remainingQty,
                'logs' => $logTooltip,
                'remarks' => $item->remarks,
                'catlogue_images' => $item->catlogue_image,
                'voice_remarks' => $item->voice_remark,
                'expected_delivery_date' => $item->expected_delivery_date,
                'fittings' => $item->fittings,
                'priority' => $item->priority_level,

                // Extra fields packed here
                'extra_type'               => $extra,
                'mens_hand_stitching'   => $item->mens_hand_stitching,
                'ladies_hand_stitching' => $item->ladies_hand_stitching,
                'shoulder_type'            => $item->shoulder_type,
                'vents'                    => $item->vents,
                'vents_required'           => $item->vents_required,
                'vents_count'              => $item->vents_count,
                'fold_cuff_required'   => $item->fold_cuff_required,
                'fold_cuff_size'       => $item->fold_cuff_size,
                'pleats_required'      => $item->pleats_required,
                'pleats_count'         => $item->pleats_count,
                'back_pocket_required' => $item->back_pocket_required,
                'back_pocket_count'    => $item->back_pocket_count,
                'adjustable_belt'      => $item->adjustable_belt,
                'suspender_button'     => $item->suspender_button,
                'trouser_position'     => $item->trouser_position,   
                'client_name_required'     => $item->client_name_required,   
                'client_name_place'     => $item->client_name_place,  
                'client_name_options'     => $item->client_name_options,   
            ];
        });
}

   

    public function resetPage($inputName){
         // Clear the input field
        $this->rows[$inputName] = '';
         // Reset validation for this input
        unset($this->rows['is_valid_'.$inputName]);
    }   

   

   

    public function openStockModal($itemId)
    {
        // Instead of: $item = $this->orderItems[$index];
        $item = collect($this->orderItems)->firstWhere('id', $itemId);
    
        if (!$item) {
            return; // safety check
        }
    
        $fabricId = isset($item['fabrics']['id']) ? $item['fabrics']['id'] : null;
        $productId = ($item['collection_id'] == 2 && isset($item['product']['id']))
                        ? $item['product']['id']
                        : null;
    
        $totalUsed = OrderStockEntry::query()
                        ->where('order_item_id', $item['id'])
                        ->when($fabricId, fn($q) => $q->where('fabric_id', $fabricId))
                        ->when($productId, fn($q) => $q->where('product_id', $productId))
                        ->sum('quantity');
    
        $defaultInputName = 'required_meter_0';
    
        $entries = OrderStockEntry::where('order_item_id', $item['id'])->get();
    
        $this->stockEntries = [];
        $initialRowsData = [];
    
        foreach ($entries as $i => $entry) {
            $entryData = $entry->toArray();
            $entryData['input_name'] = 'required_meter_' . $i;
            $entryData['is_new'] = false;
    
            $availableStock = 0;
    
            if ($item['collection_id'] == 1 && $entry->fabric_id) {
                $stockFabric = StockFabric::where('fabric_id', $entry->fabric_id)->first();
                $availableStock = $stockFabric ? $stockFabric->qty_in_meter : 0;
            } elseif ($item['collection_id'] == 2 && $entry->product_id) {
                $stockProduct = StockProduct::where('product_id', $entry->product_id)->first();
                $availableStock = $stockProduct ? $stockProduct->qty_in_pieces : 0;
            }
    
            $entryData['available_value'] = max(0, $availableStock);
    
            $initialRowsData[$entryData['input_name']] = $entryData['quantity'];
            $this->stockEntries[] = $entryData;
        }
    
        if (count($this->stockEntries) === 0) {
    
            $availableValueForDefault = 0;
    
            if ($item['collection_id'] == 1 && isset($item['fabrics']['id'])) {
                $stockFabric = StockFabric::where('fabric_id', $item['fabrics']['id'])->first();
                $availableValueForDefault = $stockFabric ? $stockFabric->qty_in_meter : 0;
            } elseif ($item['collection_id'] == 2 && isset($item['product']['id'])) {
                $stockProduct = StockProduct::where('product_id', $item['product']['id'])->first();
                $availableValueForDefault = $stockProduct ? $stockProduct->qty_in_pieces : 0;
            }
    
            $this->stockEntries[] = [
                'fabric_id' => $item['fabrics']['id'] ?? null,
                'product_id' => $item['product']['id'] ?? null,
                'quantity' => 0,
                'input_name' => $defaultInputName,
                'is_new' => true,
                'available_value' => max(0, $availableValueForDefault),
            ];
    
            $initialRowsData[$defaultInputName] = 0;
        }
    
        $this->rows = $initialRowsData;
    
        $this->selectedItem = [
            'item_id' => $item['id'],
            'collection_title' => $item['collection_title'],
            'collection_id' => $item['collection_id'],
            'product_name' => $item['product']['name'] ?? '',
            'fabric_title' => $item['fabrics']['title'] ?? '',
            'available_label' => ($item['collection_id'] == 1) ? 'Available Meter' : 'Available Pieces',
            'available_value' =>  max(0, (
                $item['collection_id'] == 1
                    ? (StockFabric::where('fabric_id', $fabricId)->first()->qty_in_meter ?? 0)
                    : (StockProduct::where('product_id', $productId)->first()->qty_in_pieces ?? 0)
            )),
            'updated_label' => ($item['collection_id'] == 1) ? 'Required Meter' : 'Required Pieces',
            'input_name' => $defaultInputName,
            'has_stock_entry' => $item['has_stock_entry'],
            'total_used' => $totalUsed,
            'fabric_id' => $fabricId,
            'product_id' => $productId
        ];
    
        $this->dispatch('open-stock-modal');
    }


    

  
    
    public function addStockEntry()
    {
        // Find item using ID instead of index
        $item = collect($this->orderItems)
                    ->firstWhere('id', $this->selectedItem['item_id']);
    
        if (!$item) {
            return;
        }
    
        $productId = $this->selectedItem['collection_id'] == 1
            ? ($item['product']['id'] ?? null)
            : null;
    
        $this->stockEntries[] = [
            'fabric_id' => null,
            'product_id' => $productId,
            'quantity' => 0,
            'is_new' => true
        ];
    }

  
    public function removeStockEntry($entryIndex)
    {
        try {
            DB::beginTransaction();

            $entry = $this->stockEntries[$entryIndex] ?? null;

            if (!$entry) {
                throw new \Exception("Entry not found.");
            }

            // If entry exists in DB, revert stock first
            if (!empty($entry['id'])) {
                $stockEntry = OrderStockEntry::find($entry['id']);

                if ($stockEntry) {
                    // Revert stock based on collection
                    if ($this->selectedItem['collection_id'] == 1 && $stockEntry->fabric_id) {
                        $stock = StockFabric::where('fabric_id', $stockEntry->fabric_id)->first();
                        if ($stock) {
                            $stock->increment('qty_in_meter', $stockEntry->quantity);
                        }
                    } elseif ($this->selectedItem['collection_id'] == 2 && $stockEntry->product_id) {
                        $stock = StockProduct::where('product_id', $stockEntry->product_id)->first();
                        if ($stock) {
                            $stock->increment('qty_in_pieces', $stockEntry->quantity);
                        }
                    }

                    // Delete stock entry from DB
                    $stockEntry->delete();
                }
            }

            // Remove from stockEntries (Livewire data)
            unset($this->stockEntries[$entryIndex]);
            $this->stockEntries = array_values($this->stockEntries); // Reindex

            DB::commit();
            session()->flash('success', 'Stock entry removed and stock updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to remove stock entry: ' . $e->getMessage());
        }
    }


    public function searchFabric($entryIndex)
    {
        $searchTerm = $this->fabricSearch[$entryIndex] ?? '';
        $productId = $this->stockEntries[$entryIndex]['product_id'] ?? null;
        if ($searchTerm && $productId) {
            $this->searchResults[$entryIndex] = Fabric::join('product_fabrics', 'fabrics.id', '=', 'product_fabrics.fabric_id')
                ->where('product_fabrics.product_id', $productId)
                ->where('fabrics.status', 1)
                ->where('fabrics.title', 'LIKE', "%{$searchTerm}%")
                ->select('fabrics.id', 'fabrics.title')
                ->distinct()
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->searchResults[$entryIndex] = [];
        }
    }

    public function selectFabric($fabricId,$entryIndex)
    {
        $fabric = Fabric::find($fabricId);

        if ($fabric) {
            $this->stockEntries[$entryIndex]['fabric_id'] = $fabric->id;
            $this->fabricSearch[$entryIndex] = $fabric->title;

            // Fetch available meter from stock fabric
            $stock = StockFabric::where('fabric_id', $fabric->id)->first();
            if ($stock) {
                $this->stockEntries[$entryIndex]['available_value'] = (int)$stock->qty_in_meter ?? 0;
            } else {
                $this->stockEntries[$entryIndex]['available_value'] = 0;
            }
            // Optional: clear searchResults if you want to hide the list after selection
            $this->searchResults[$entryIndex] = [];
        }
    }

    public function updatedFabricSearch($value, $key)
    {
        $index = explode('.', $key)[0]; // Get the $entryIndex

        if (empty($value)) {
            // Reset available_value and fabric_id if search cleared
            $this->stockEntries[$index]['available_value'] = 0;
            $this->stockEntries[$index]['fabric_id'] = null;
            $this->searchResults[$index] = [];
        }
    }

    

   
    
    public function openGarmentDeliveryModal($itemId)
{
    // Instead of: $item = $this->orderItems[$index];
    $item = collect($this->orderItems)->firstWhere('id', $itemId);

    if (!$item) {
        return;
    }

    $fabricId = $item['fabrics']['id'] ?? null;
    $productId = $item['collection_id'] == 2
                    ? ($item['product']['id'] ?? null)
                    : null;

    $totalUsed = OrderStockEntry::query()
                    ->where('order_item_id', $item['id'])
                    ->when($fabricId, fn($q) => $q->where('fabric_id', $fabricId))
                    ->when($productId, fn($q) => $q->where('product_id', $productId))
                    ->sum('quantity');

    $defaultInputName = 'required_meter_0';

    $entries = OrderStockEntry::where('order_item_id', $item['id'])->get();

    $this->stockEntries = [];
    $this->deliveryEntries = [];
    $initialRowsData = [];

    foreach ($entries as $i => $entry) {

        $entryData = $entry->toArray();
        $entryData['input_name'] = 'required_meter_' . $i;
        $entryData['is_new'] = false;

        $availableStock = 0;

        if ($item['collection_id'] == 1 && $entry->fabric_id) {
            $stockFabric = StockFabric::where('fabric_id', $entry->fabric_id)->first();
            $availableStock = $stockFabric ? $stockFabric->qty_in_meter : 0;
        } elseif ($item['collection_id'] == 2 && $entry->product_id) {
            $stockProduct = StockProduct::where('product_id', $entry->product_id)->first();
            $availableStock = $stockProduct ? $stockProduct->qty_in_pieces : 0;
        }

        $entryData['available_value'] = max(0, $availableStock);

        $initialRowsData[$entryData['input_name']] = $entryData['quantity'];

        $this->deliveryEntries[$i]['delivered_meter'] = (int) ($entry->extra_meter ?? 0);

        $entryData['extra_meter'] = $entry->extra_meter ?? 0;

        $this->stockEntries[] = $entryData;
    }

    if (count($this->stockEntries) === 0) {

        $this->deliveryEntries[0] = ['delivered_meter' => 0];

        $availableValueForDefault = 0;

        if ($item['collection_id'] == 1 && isset($item['fabrics']['id'])) {
            $stockFabric = StockFabric::where('fabric_id', $item['fabrics']['id'])->first();
            $availableValueForDefault = $stockFabric ? $stockFabric->qty_in_meter : 0;
        } elseif ($item['collection_id'] == 2 && isset($item['product']['id'])) {
            $stockProduct = StockProduct::where('product_id', $item['product']['id'])->first();
            $availableValueForDefault = $stockProduct ? $stockProduct->qty_in_pieces : 0;
        }

        $this->stockEntries[] = [
            'fabric_id' => $item['fabrics']['id'] ?? null,
            'product_id' => $item['product']['id'] ?? null,
            'quantity' => 0,
            'input_name' => $defaultInputName,
            'is_new' => true,
            'available_value' => max(0, $availableValueForDefault),
        ];

        $initialRowsData[$defaultInputName] = 0;
    }

    $this->rows = $initialRowsData;

    $this->selectedItem = [
        'item_id' => $item['id'],
        'collection_title' => $item['collection_title'],
        'collection_id' => $item['collection_id'],
        'product_name' => $item['product']['name'] ?? '',
        'fabric_title' => $item['fabrics']['title'] ?? '',
        'available_label' => ($item['collection_id'] == 1) ? 'Available Meter' : 'Available Pieces',
        'available_value' =>  max(0, (
            $item['collection_id'] == 1
                ? (StockFabric::where('fabric_id', $fabricId)->first()->qty_in_meter ?? 0)
                : (StockProduct::where('product_id', $productId)->first()->qty_in_pieces ?? 0)
        )),
        'updated_label' => ($item['collection_id'] == 1) ? 'Required Meter' : 'Required Pieces',
        'input_name' => $defaultInputName,
        'has_stock_entry' => $item['has_stock_entry'],
        'total_used' => $totalUsed,
        'fabric_id' => $fabricId,
        'product_id' => $productId
    ];

    $this->dispatch('open-garment-delivery-modal');
}


    

    // New
    public function addDeliveryRow($entryIndex)
    {
        $delivered = (float)($this->deliveryEntries[$entryIndex]['delivered_meter'] ?? 0);
        $available = (float)($this->stockEntries[$entryIndex]['available_value'] ?? 0);
        $inputName = $this->stockEntries[$entryIndex]['input_name'];

        if ($delivered <= 0) {
            $this->addError("deliveryEntries.$entryIndex.delivered_meter", "Delivered meter must be greater than 0.");
            return;
        }

        if ($delivered > $available) {
            $this->addError("deliveryEntries.$entryIndex.delivered_meter", "Delivered exceeds available stock.");
            return;
        }

        $orderItemId = $this->selectedItem['item_id'];
        $fabricId = $this->stockEntries[$entryIndex]['fabric_id'];

        // 1. Update or create OrderStockEntry
        $entry = OrderStockEntry::where('order_item_id', $orderItemId)
            ->where('fabric_id', $fabricId)
            ->first();

        if ($entry) {
            $entry->quantity += $delivered;
            $entry->extra_meter += $delivered;
            $entry->save();
        } else {
            OrderStockEntry::create([
                'order_id'     => $this->selectedItem['order_id'],
                'order_item_id'=> $orderItemId,
                'product_id'   => $this->selectedItem['product_id'],
                'fabric_id'    => $fabricId,
                'quantity'     => $delivered,
                'extra_meter'  => $delivered,
                'unit'         => 'meter',
                'created_by'   => auth()->id(),
            ]);
        }

        // 2. Update stock fabric
        $stock = StockFabric::where('fabric_id', $fabricId)->first();
        if ($stock) {
            $stock->qty_in_meter -= $delivered;
            $stock->save();
        }

        // 3. Refresh OrderStockEntry to get updated extra_meter
        $updatedEntry = OrderStockEntry::where('order_item_id', $orderItemId)
            ->where('fabric_id', $fabricId)
            ->first();

        $updatedExtraMeter = $updatedEntry?->extra_meter ?? 0;

        // 4. Update UI state
        $this->rows[$inputName] += $delivered;
        $this->stockEntries[$entryIndex]['available_value'] = max(0, $available - $delivered);

        // ✅ Update the UI with the updated extra_meter from DB
        $this->stockEntries[$entryIndex]['extra_meter'] = $updatedExtraMeter;
        $this->deliveryEntries[$entryIndex]['delivered_meter'] = (int)$updatedExtraMeter;
        $this->deliveryEntries[$entryIndex]['required_meter'] = $this->rows[$inputName];

        if ($entryIndex === 0) {
            $this->selectedItem['available_value'] = $this->stockEntries[$entryIndex]['available_value'];
        }

    }

        public function openDeliveryModal($index)
    {
       $orderItems = $this->orderItems->values()->all();
        $item =  $orderItems[$index];

        $plannedUsage = 0;
        $unit = '';
        $fabricId = null;
        $productId = null;

        $stockProduct = 0;
        if ($item['collection_id'] == 1) {
            $fabricId = $item['fabrics']->id ?? null;
            $plannedUsage = OrderStockEntry::query()
                ->where('order_item_id', $item['id'])
                ->when($fabricId, fn($q) => $q->where('fabric_id', $fabricId))
                ->sum('quantity');  
             $unit = 'meters';
            if (!isset($this->actualUsage[$item['id']])) {
                $this->actualUsage[$item['id']] = $plannedUsage;
            }
        } elseif ($item['collection_id'] == 2) {
            $productId = $item['product']->id ?? null;
            $stockProduct = StockProduct::where('product_id',$productId)->sum('qty_in_pieces');
            $plannedUsage = $item['quantity'];
            $unit = 'pieces';

            //  Calculate already delivered and remaining
            $alreadyDelivered = Delivery::where('order_item_id', $item['id'])->sum('delivered_quantity');
            $remainingToDeliver = $plannedUsage - $alreadyDelivered;
            // For collection_id == 2, prefill actualUsage:
            $this->actualUsage[$item['id']] = $remainingToDeliver;
        }

        $this->selectedDeliveryItem = [
            'item_id' => $item['id'],
            'index' => $index,
            'collection_id' => $item['collection_id'],
            'collection_title' => $item['collection_title'],
            'product_name' => $item['product']['name'] ?? '',
            'fabric_title' => $item['fabrics']['title'] ?? '',
            'product_id'   => $productId,
            'fabric_id'    => $fabricId,
            'planned_usage' => $plannedUsage,
            'stock_product' => $stockProduct,
            'unit' => $unit,

            'ordered_quantity' => $plannedUsage,
            'delivered_quantity' => $alreadyDelivered ?? 0,
            'remaining_to_deliver' => $remainingToDeliver ?? 0,
        ];
        // dd($this->selectedDeliveryItem);

        $this->dispatch('open-delivery-modal');
    }



        public function checkActualUsage()
    {
        if ($this->selectedDeliveryItem['collection_id'] == 1) {
            $planned = $this->selectedDeliveryItem['planned_usage'] ?? 0;
            $itemId = $this->selectedDeliveryItem['item_id'] ?? null;
            $actual = floatval($this->actualUsage[$itemId] ?? 0);

            $this->showExtraStockPrompt = $actual > $planned;
        } else {
            $this->showExtraStockPrompt = false;
        }
    }



    public function updatedActualUsage()
    {
        $planned = $this->selectedDeliveryItem['planned_usage'] ?? 0;
        $this->showExtraStockPrompt = $this->actualUsage > $planned;
    }


    public function addExtraStock()
{
    $index = $this->selectedDeliveryItem['index'] ?? null;

    if ($index !== null) {
        $item = $this->orderItems->get($index);

        $itemId = $item['id'];
        $collectionId = $item['collection_id'];
        $fabricId = $collectionId == 1 ? ($item['fabrics']->id ?? null) : null;
        $productId = $collectionId == 2 ? ($item['product']->id ?? null) : null;

        $actualQty = $this->actualUsage[$itemId] ?? 0;
        $currentUsage = $this->selectedDeliveryItem['planned_usage'] ?? 0;

        if ($actualQty <= $currentUsage) {
            return;
        }

        $availableStock = $item['stock_entry_data']['available_value'] ?? 0;
        $extraQty = $actualQty - $currentUsage;

        if ($extraQty > $availableStock) {
            session()->flash('stock_error', 'Entered extra quantity exceeds available stock.');
            return;
        }

        DB::beginTransaction();

        try {
            //  Update or create stock entry
            $stockEntry = OrderStockEntry::where('order_item_id', $itemId)->first();

            if ($stockEntry) {
                $stockEntry->update(['quantity' => $actualQty]); // overwrite with new total usage
            } else {
                OrderStockEntry::create([
                    'order_id' => $this->orderId,
                    'order_item_id' => $itemId,
                    'fabric_id' => $fabricId,
                    'product_id' => $productId,
                    'quantity' => $actualQty,
                    'unit' => $item['stock_entry_data']['type'],
                    'created_by' => auth()->guard('admin')->user()->id,
                ]);
            }

            //  Update physical stock
            if ($collectionId == 1 && $fabricId) {
                StockFabric::where('fabric_id', $fabricId)->decrement('qty_in_meter', $extraQty);
            } elseif ($collectionId == 2 && $productId) {
                StockProduct::where('product_id', $productId)->decrement('qty_in_pieces', $extraQty);
            }

            //  Log the change (optional)
            ChangeLog::create([
                'done_by' => auth()->guard('admin')->user()->id,
                'purpose' => 'extra_stock_entry',
                'data_details' => json_encode([
                    'order_item_id' => $itemId,
                    'extra_quantity' => $extraQty,
                ]),
            ]);

            DB::commit();

            //  Update frontend state
            $item['stock_entry_data']['available_value'] -= $extraQty;
            $item['stock_entry_data']['updated_label'] = $actualQty;
            $this->orderItems->put($index, $item);

            $this->selectedDeliveryItem['planned_usage'] = $actualQty;
            $this->actualUsage[$itemId] = $actualQty;
            $this->showExtraStockPrompt = false;

            session()->forget('stock_error');
            $this->dispatch('stock-updated'); // optional for UI refresh

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            session()->flash('stock_error', 'Something went wrong. Please try again.');
        }
    }
}



   

    public function processDelivery()
{
    // Decide which item to process
    $item = ($this->selectedDeliveryItem['collection_id'] ?? null) == 2
        ? $this->selectedDeliveryItem
        : $this->selectedItem;

    $itemId = $item['item_id'] ?? $item['id'] ?? null;
    $collectionId = $item['collection_id'] ?? null;

    // ========== FOR COLLECTION 2 (PRODUCTS) ==========
    if ($collectionId == 2) {
        $this->validate([
            'actualUsage.' . $itemId => 'required|numeric|min:1',
        ], [
            'actualUsage.*.required' => 'Please enter the actual usage.',
            'actualUsage.*.numeric'  => 'The actual usage must be a number.',
            'actualUsage.*.min'      => 'The actual usage must be at least 1.',
        ]);

        $actual = floatval($this->actualUsage[$itemId]);
        $orderedQty = floatval($item['ordered_quantity'] ?? 0);
        $alreadyDelivered = floatval($item['delivered_quantity'] ?? 0);
        $remainingToDeliver = $orderedQty - $alreadyDelivered;
        $availableStock = floatval($item['stock_product'] ?? 0);

        if ($actual > $remainingToDeliver) {
            session()->flash('stock_error', 'You are trying to deliver more (' . $actual . ') than remaining quantity (' . $remainingToDeliver . ').');
            return;
        }

        if ($availableStock < $actual) {
            session()->flash('stock_error', 'Available stock (' . $availableStock . ') is less than entered quantity (' . $actual . ').');
            return;
        }
    }

    // ========== FOR COLLECTION 1 (FABRIC) ==========
    if ($collectionId == 1) {
        // Only take latest matching stock entries for this delivery
        $this->stockEntries = collect($this->stockEntries)
            ->where('order_id', $this->orderId)
            ->where('order_item_id', $itemId)
            ->sortBy('id') // ensure delivery order
            ->values()
            ->toArray();

        $totalDelivered = 0;

        foreach ($this->stockEntries as $entry) {
            $requiredQty = floatval($entry['quantity'] ?? 0);
            if ($requiredQty > 0) {
                $totalDelivered += $requiredQty;
            }
        }

        if ($totalDelivered <= 0) {
            session()->flash('stock_error', 'Please enter a valid delivery quantity.');
            return;
        }
    }

    // ========== TRANSACTION START ==========
    DB::beginTransaction();
    try {
        $orderItemModel = OrderItem::find($itemId);

        // ========= Process PRODUCT DELIVERY (COLLECTION 2) =========
        if ($collectionId == 2) {
            $actual = floatval($this->actualUsage[$itemId]);

            Delivery::create([
                'order_id' => $this->orderId,
                'order_item_id' => $itemId,
                'product_id' => $item['product_id'] ?? null,
                'delivered_quantity' => $actual,
                'unit' => 'pieces',
                'delivered_by' => auth()->guard('admin')->user()->id,
                'delivered_at' => now(),
            ]);

            $productStock = StockProduct::where('product_id', $item['product_id'])->first();
            if ($productStock) {
                $productStock->decrement('qty_in_pieces', $actual);
            }
        }

        // ========= Process FABRIC DELIVERY (COLLECTION 1) =========
        if ($collectionId == 1) {
            foreach ($this->stockEntries as $index => $entry) {
               // dd($this->rows["required_meter_" . $index]);
               
                $fabricId = $entry['fabric_id'] ?? null;
                //$requiredQty = floatval($entry['quantity'] ?? 0); // Take per stock entry quantity
                $requiredQty = $this->rows['required_meter_'. $index];
                
                if ($requiredQty > 0 && $fabricId) {
                    Delivery::create([
                        'order_id' => $this->orderId,
                        'order_item_id' => $itemId,
                        'fabric_id' => $fabricId,
                        'fabric_quantity' => $requiredQty,
                        'delivered_quantity' => $requiredQty, // per delivery
                        'unit' => 'meters',
                        'delivered_by' => auth()->guard('admin')->user()->id,
                        'delivered_at' => now(),
                    ]);
                }
            }
        }

        // ========= Change Log =========
        $logDetails = [
            'order_id' => $this->orderId,
            'order_item_id' => $itemId,
            'collection_id' => $collectionId,
            'unit' => $item['unit'] ?? ($collectionId == 2 ? 'pieces' : 'meters'),
            'timestamp' => now(),
        ];

        if ($collectionId == 1) {
            $fabricLogs = [];
            foreach ($this->stockEntries as $index => $entry) {
                $fabricId = $entry['fabric_id'] ?? null;
                //$requiredQty = floatval($entry['quantity'] ?? 0);
                $requiredQty = $this->rows['required_meter_'. $index];
                $extraQty = floatval($entry['extra_meter'] ?? 0);

                if ($requiredQty > 0 && $fabricId) {
                    $fabric = Fabric::find($fabricId);
                    $fabricName = $fabric ? $fabric->title : 'Unknown Fabric';

                    $fabricLogs[] = [
                        'fabric' => $fabricName,
                        'delivered_quantity' => $requiredQty,
                        'extra_meter' => $extraQty
                    ];
                }
            }
            $logDetails['delivered_fabrics'] = $fabricLogs;
        } elseif ($collectionId == 2 && isset($actual)) {
            $logDetails['delivered_quantity'] = $actual;
        }

        Changelog::create([
            'done_by' => auth()->guard('admin')->user()->id,
            'purpose' => 'delivery_proceed',
            'order_id' => $this->orderId,
            'data_details' => json_encode($logDetails),
        ]);

        // ========= Update Order Status =========
        $order = Order::find($this->orderId);
        $items = $order->items;

        $allDelivered = true;
        $anyDelivered = false;

        $productionItems = $items->filter(fn($itm) => $itm->assigned_team === 'production');
        foreach ($productionItems as $itm) {
            if ($itm->collection == 2) {
                $deliveredQty = Delivery::where('order_item_id', $itm->id)->sum('delivered_quantity');
                if ($deliveredQty >= $itm->quantity) {
                    $anyDelivered = true;
                } else {
                    $allDelivered = false;
                    if ($deliveredQty > 0) {
                        $anyDelivered = true;
                    }
                }
            } else {
                $hasDelivery = Delivery::where('order_item_id', $itm->id)->exists();
                if ($hasDelivery) {
                    $anyDelivered = true;
                } else {
                    $allDelivered = false;
                }
            }
        }

        if ($productionItems->count() > 0) {
            if ($allDelivered) {
                $order->update(['status' => 'Fully Delivered By Production']);
            } elseif ($anyDelivered) {
                $order->update(['status' => 'Partial Delivered By Production']);
            }
        }

        DB::commit();

        unset($this->actualUsage[$itemId]);
        $this->loadOrderItems();
        $this->dispatch('close-delivery-modal');

        return redirect()->route('production.order.details', $this->orderId);
    } catch (\Throwable $e) {
        dd($e->getMessage());
        DB::rollBack();
    }
}



    public function updated($propertyName, $value)
{
    // Match pattern: rows.required_meter_0, rows.required_meter_1, etc.
    if (preg_match('/^rows\.required_meter_(\d+)$/', $propertyName, $matches)) {
        $index = (int) $matches[1];
        
        // Only update if delivered_meter is 0 or null (i.e., not manually changed)
        if (!isset($this->deliveryEntries[$index]['delivered_meter']) || $this->deliveryEntries[$index]['delivered_meter'] == 0) {
            $this->deliveryEntries[$index]['delivered_meter'] = $value;
        }
    }
}


    public function render()
    {
        
         // Fetch product details for each order item
         $this->loadOrderItems();
        return view('livewire.order.production-order-details',[
            //  'order' => $this->order,
            'orderItems' => $this->orderItems,
            'latestOrders'=>$this->latestOrders,
        ]);
    }
}
