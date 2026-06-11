<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackingSlip;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\InvoiceProduct;
use App\Models\PaymentCollection;
use App\Models\StockFabric;
use App\Models\StockProduct;
use App\Models\Delivery;
use App\Models\Measurement;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\AccountingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AddOrderSlip extends Component
{
    protected $accountingRepository;
    public $order,$orderId;
    public $errorMessage = [];
    public $order_item = [];
    public $activePayementMode = 'cash';
    public $staffs =[];
    public $from_date;
    public $to_date;
    public $document_type = 'invoice';
    public $payment_collection_id = "";
    public $readonly = "readonly";
    public $customer,$customer_id, $staff_id,$staff_name, $total_amount, $actual_amount, $voucher_no, $payment_date, $payment_mode, $chq_utr_no, $bank_name, $receipt_for = "Customer",$amount;
    public $air_mail;

    public function boot(AccountingRepositoryInterface $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }
    public function mount($id){
        $this->orderId=$id;
        $this->order = Order::with('items.measurements','items.fabric','customer','createdBy')->where('id', $id)->first();
        if($this->order){
            foreach($this->order->items as $key=>$order_item){
                 
               $product =  $order_item->product ?? null;

               $this->order_item[$key] = [
                'id' => $order_item->id,
                'product_name' => $order_item->product_name ?? ($product ? $product->name : null),
                'collection_id' => $order_item->collection,
               'collection_title' => $order_item->collectionType ? $order_item->collectionType->title : '',
                'fabrics' => $order_item->fabric,
                'team' => $order_item->assigned_team ?? 'production',
                'tl_approved' => $order_item->tl_status == 'Approved',
                'admin_approved' => $order_item->admin_status == 'Approved',
                'measurements' => $order_item->measurements->map(function ($m) {
                    return [
                        'measurement_name' => $m->name,
                        'measurement_title_prefix' => $m->title_prefix,
                        'measurement_value' => $m->value,
                    ];
                })->toArray(),
                'catalogue' => $order_item->catalogue_id ? $order_item->catalogue : '',
                'catalogue_id' => $order_item->catalogue_id,
                'cat_page_number' => $order_item->cat_page_number,
                'piece_price' => (int) $order_item->piece_price,
                'quantity' => $order_item->quantity,
                'remarks' => $order_item->remarks,
                'catlogue_image' => $order_item->catlogue_image,
                'voice_remark' => $order_item->voice_remark,
                'priority_level' => $order_item->priority_level,
             ];
                
            }
             $this->total_amount = $this->order->items
            ->where('status', '!=', 'Hold')
            ->sum(function ($item) {
                return $item->piece_price * $item->quantity;
            });
            $this->actual_amount = $this->total_amount;
            
            // $this->total_amount = $this->order->total_amount;
            // $this->actual_amount = $this->order->total_amount;
            $this->air_mail = $this->order->air_mail;
            $this->customer = optional($this->order->customer)->name;
            $this->customer_id = optional($this->order->customer)->id;
            $this->staff_id = optional($this->order->createdBy)->id;
            $this->staff_name = optional($this->order->createdBy)->name;
            $this->payment_date = date('Y-m-d');
        }else{
            abort(404);
        }

        $this->voucher_no = 'PAYRECEIPT'.time();
        $this->staffs = User::where('user_type', 0)->where('designation', 2)->select('name', 'id')->orderBy('name', 'ASC')->get();
    }

    public function hasTeamSelected()
    {
        foreach ($this->order_item as $item) {
            if (!empty($item['team'])) {
                return true;
            }
        }
        return false;
    }

  
    
      public function setTeamAndSubmit()
    {
        if (!$this->hasTeamSelected()) {
            session()->flash('error', 'Please select at least one team before submitting.');
            return;
        }
    
        $userDesignationId = auth()->guard('admin')->user()->designation;
    
        foreach ($this->order_item as $key => $itemData) {
    
            $item = OrderItem::find($itemData['id']);
            if (!$item) continue;
    
            /* ------------------------------
             | 1. Assign Team
             ------------------------------ */
            if (!empty($itemData['team'])) {
                $item->assigned_team = $itemData['team'];
                  // if item data go to production then set received at 
               if ($itemData['team'] === 'production') {
                    $item->received_at = now();
                }
            }
    
           
        
            /* ------------------------------
             | 3. Admin Approval (designation = 1)
             ------------------------------ */
            if ($userDesignationId == 1) {
                // Admin can only approve if TL has approved
                if (!empty($itemData['admin_approved']) && $item->tl_approved) {
                    $item->admin_status = 'Approved';
                } else {
                    $item->admin_status = 'Hold';
                }
            }
    
            $item->save();
    
            /* ------------------------------
             | 4. Auto Delivery for Sales Team
             ------------------------------ */
            if (!empty($itemData['team']) && $itemData['team'] === 'sales') {
    
                $this->autoDeliverItem($item);
    
                $delivery = Delivery::where('order_item_id', $item->id)->latest()->first();
    
                if ($delivery) {
                    if ($delivery->status !== 'Delivered') {
                        $delivery->update([
                            'status'       => 'Received by Sales Team',
                            'remarks'      => 'Auto-updated as received by Sales Team',
                            'delivered_at' => now(),
                        ]);
                    }
                } else {
                    if ($item->collection == 1) {
                        Delivery::create([
                            'order_id'           => (int) $item->order_id,
                            'order_item_id'      => (int) $item->id,
                            'fabric_id'          => is_numeric($item->fabrics) ? (int) $item->fabrics : null,
                            'fabric_quantity'    => (float) $item->quantity,
                            'delivered_quantity' => (float) $item->quantity,
                            'unit'               => 'meters',
                            'status'             => 'Received by Sales Team',
                            'delivered_by'       => auth()->guard('admin')->id(),
                            'remarks'            => 'Auto-delivered to Sales Team (Fabric)',
                            'delivered_at'       => now(),
                        ]);
                    }
    
                    if ($item->collection == 2) {
                        Delivery::create([
                            'order_id'           => (int) $item->order_id,
                            'order_item_id'      => (int) $item->id,
                            'product_id'         => is_numeric($item->product_id) ? (int) $item->product_id : null,
                            'delivered_quantity' => (float) $item->quantity,
                            'unit'               => 'pieces',
                            'status'             => 'Received by Sales Team',
                            'delivered_by'       => auth()->guard('admin')->id(),
                            'remarks'            => 'Auto-delivered to Sales Team (Product)',
                            'delivered_at'       => now(),
                        ]);
                    }
                }
            }
        }
    
        /* ------------------------------
         | 5. Update Order Status if Fully Approved
         ------------------------------ */
        $allApproved = OrderItem::where('order_id', $this->order->id)
            ->where('admin_status', '!=', 'Approved')
            ->doesntExist();
    
        if ($allApproved) {
            Order::where('id', $this->order->id)
                ->update(['status' => 'Fully Approved By Admin']);
        }
    
        return $this->submitForm();
    }





 

    protected function autoDeliverItem(OrderItem $item)
    {
        // Check if delivery already exists for this item
        $existingDelivery = Delivery::where('order_item_id',$item->id)->exists();
        if($existingDelivery){
            return; // Prevent duplicate delivery
        }
        if ($item->collection == 1) {
            // Fabric collection
            $fabricStock = StockFabric::where('fabric_id', $item->fabrics)->first();
            if ($fabricStock && $fabricStock->qty_in_meter >= $item->quantity) {
                $fabricStock->qty_in_meter -= $item->quantity;
                $fabricStock->save();

                Delivery::create([
                    'order_id' => $item->order_id,
                    'order_item_id' => $item->id,
                    'fabric_id' => $item->fabrics,
                    'fabric_quantity' => $item->quantity,
                    'delivered_quantity' => $item->quantity,
                    'unit' => 'meters',
                    'delivered_by' => auth()->guard('admin')->user()->id,
                    'delivery_date' => now(),
                     'status'  => 'Delivered',
                ]);
            }
        } elseif ($item->collection == 2) {
            // Product collection
            $productStock = StockProduct::where('product_id', $item->product_id)->first();
            if ($productStock && $productStock->qty_in_pieces >= $item->quantity) {
                $productStock->qty_in_pieces -= $item->quantity;
                $productStock->save();

                Delivery::create([
                    'order_id' => $item->order_id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'delivered_quantity' => $item->quantity,
                    'fabric_quantity'  => null,
                    'unit' => 'pieces',
                    'delivered_by' => auth()->guard('admin')->user()->id,
                    'delivery_date' => now(),
                      'status'                    => 'Delivered',
                ]);
            }
        }
    }




    public function updateQuantity($value, $key,$price){
        if(!empty($value)){
            $this->order_item[$key]['quantity']= $value;
            $base_price = $price * $value;
            $this->order_item[$key]['price'] = $base_price;

            $subtotal = 0;
            foreach ($this->order_item as $item) {
                $subtotal += $item['price'];
            }

            // Add the air_mail from the Order, not items
           $this->actual_amount = $subtotal + $this->air_mail;
        }
    }

  
    
    public function submitForm()
{
   // dd($this->all());
    $this->reset(['errorMessage']);
    $this->errorMessage = [];

    // -----------------------------
    // 1. Validate order items
    // -----------------------------
    foreach ($this->order_item as $key => $item) {
        if (!isset($item['air_mail'])) $item['air_mail'] = 0;
        if (empty($item['quantity'])) {
            $this->errorMessage["order_item.$key.quantity"] = 'Please enter quantity.';
        }
    }

    // Validate customer
    if (empty($this->customer_id)) $this->errorMessage['customer_id'] = 'Please select a customer.';

    // Validate collected by
    if (empty($this->staff_id)) $this->errorMessage['staff_id'] = 'Please select a staff member.';

    if (count($this->errorMessage) > 0) return $this->errorMessage;

    DB::beginTransaction();

    try {
        $userDesignationId = auth()->guard('admin')->user()->designation;

        // -----------------------------
        // 2. TL Approval Logic
        // -----------------------------
               if ($userDesignationId == 4) { 
                    foreach ($this->order_item as $key => $itemData) {
        
                // Validate priority when TL approves
                if (($itemData['tl_approved'] ?? false) && empty($itemData['priority_level'])) {
                    $this->addError(
                        "order_item.$key.priority_level",
                        'Please select priority.'
                    );
                }
            }
        
            // Stop execution if validation errors exist
          if ($this->getErrorBag()->isNotEmpty()) {
                $this->dispatch('scroll-to-error');
                return;
            }
            
            foreach ($this->order_item as $key => $itemData) {
                $item = OrderItem::find($itemData['id']);
                if (!$item) continue;
        
                // Update TL approval
                $item->priority_level = $itemData['priority_level'] ?? null;
        
                if ($itemData['tl_approved'] ?? false) {
                    $item->tl_status = 'Approved';
                    $item->status = 'Process'; // promote if needed
                } else {
                    $item->tl_status = 'Pending';
                    // Keep current status
                }
        
                $item->save();
        
                // Keep Livewire array in sync
                $this->order_item[$key]['tl_status'] = $item->tl_status;
                $this->order_item[$key]['priority_level'] = $item->priority_level;
            }
        
            $hasProcessItem = OrderItem::where('order_id', $this->order->id)
                ->where('status', 'Process')
                ->where('tl_status', 'Approved')
                ->whereNotNull('priority_level')
                ->exists();
        
            if (!$hasProcessItem) {
                DB::rollBack();
                session()->flash('error', 'Cannot approve order. No items are approved by Team Leader.');
                return redirect()->route('admin.order.add_order_slip', $this->order->id);
            }
        }


        // -----------------------------
        // 3. Admin Approval Logic
        // -----------------------------
        if ($userDesignationId == 1) {
            foreach ($this->order_item as $key => $itemData) {
                $item = OrderItem::find($itemData['id']);
                if (!$item) continue;
        
                // Update TL approval
                $item->priority_level = $itemData['priority_level'] ?? null;
        
                if ($itemData['admin_approved'] ?? false) {
                    $item->admin_status = 'Approved';
                    $item->status = 'Process'; // promote if needed
                } else {
                    $item->admin_status = 'Pending';
                    // Keep current status
                }
        
                $item->save();
        
                // Keep Livewire array in sync
                $this->order_item[$key]['tl_status'] = $item->tl_status;
                 $this->order_item[$key]['admin_status'] = $item->admin_status;
                $this->order_item[$key]['priority_level'] = $item->priority_level;
            }
            $hasProcessItem = OrderItem::where('order_id', $this->order->id)
                ->where('status', 'Process')
                ->where('tl_status', 'Approved')
                ->where('admin_status', 'Approved')
                ->exists();

            if (!$hasProcessItem) {
                DB::rollBack();
                session()->flash('error', 'Cannot approve order. No items are approved by Admin.');
                return redirect()->route('admin.order.add_order_slip', $this->order->id);
            }
        }

        // -----------------------------
        // 4. Update Order and Items
        // -----------------------------
        $this->updateOrder();
        $this->updateOrderItems();

        // -----------------------------
        // 5. Create Packing Slip
        // -----------------------------
        $this->createPackingSlip();

        DB::commit();
        session()->flash('success', 'Order approved successfully.');
        return redirect()->route('admin.order.index');

    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('error', $e->getMessage());
        return redirect()->route('admin.order.add_order_slip', $this->order->id);
    }
}

    
  
    public function updateOrder()
    {
        $this->validate([
            'total_amount' => 'required|numeric',
            'customer_id'  => 'required|exists:users,id',
            'staff_id'     => 'required|exists:users,id',
        ]);

        $order = Order::find($this->order->id);
        $userDesignationId = auth()->guard('admin')->user()->designation;
        $status = null;
        if ($order) {
            if ($userDesignationId == 4) {
                // Count all process items
                $processItemsCount = $order->items()->where('status', 'Process')->count();

                // Count process items not yet approved by TL
                $pendingItemsCount = $order->items()
                ->where(function ($q) {
                    $q->where('status', 'Hold')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'Process')
                            ->where(function ($q3) {
                                $q3->whereNull('tl_status')
                                    ->orWhere('tl_status', '!=', 'Approved');
                            });
                    });
                })
                ->count();

                if ($pendingItemsCount == 0 && $processItemsCount > 0) {
                    $status = "Fully Approved By TL";
                } else {
                    $status = "Partial Approved By TL";
                }
            }
            elseif ($userDesignationId == 1) { // Admin
                // Total items that are either Process or Hold
                $allRelevantItems = $order->items()
                    ->whereIn('status', ['Process', 'Hold'])
                    ->get();

                $totalItems = $allRelevantItems->count();

                // Count of items Admin approved and TL approved
                $adminApprovedCount = $allRelevantItems
                    ->where('tl_status', 'Approved')
                    ->where('admin_status', 'Approved')
                    ->whereNotNull('assigned_team') 
                    ->count();

                
                
                $hasPending = $allRelevantItems->contains(function ($item) {
                    return $item->admin_status !== 'Approved';
                });
                

                if ($adminApprovedCount == 0) {
                    $status = "Approval Pending from TL";
                } elseif ($hasPending > 0) {
                    $status = "Partial Approved By Admin";
                } elseif ($adminApprovedCount == $totalItems) {
                    $status = "Fully Approved By Admin";
                }
            }
            else {
                $status = "Approval Pending from TL";
            }

            $order->update([
                'customer_id'       => $this->customer_id,
                'created_by'        => $this->staff_id,
                'status'            => $status,
                'last_payment_date' => $this->payment_date,
            ]);
        }
    }


  


    public function updateOrderItems()
    {
            $subtotal = 0;
            foreach ($this->order_item as $key=> $item) {
                $piecePrice = (float)$item['piece_price'];
                $quantity = (int)$item['quantity'];
                $totalPrice = $piecePrice * $quantity;

                OrderItem::where('id', $item['id'])->update([
                    'total_price' => $totalPrice,
                    'quantity' => $quantity,
                    'piece_price' => $piecePrice,
                    'priority_level'  => $item['priority_level'] ?? null
                ]);

                $subtotal += $totalPrice;
            }

            // Get the Order's air_mail
            $order = Order::find($this->order->id);
            $air_mail = $order->air_mail ?? 0;
            $total_amount = $subtotal + $air_mail;

            // Update the Order's total_amount
            $order->update(['total_amount' => $total_amount]);
    }
  

    public function createPackingSlip()
    {
        $order = Order::find($this->order->id);

        if ($order) {
            // 1. Check if a packing slip already exists for this order
            $packingSlip = PackingSlip::where('order_id', $order->id)->first();

            if (!$packingSlip) {
                // If not exists, create new
                $packingSlip = PackingSlip::create([
                    'order_id' => $order->id,
                    'customer_id' => $this->customer_id,
                    'slipno' => $order->order_number,
                    'is_disbursed' => 0,
                    'created_by' => $this->staff_id,
                    'disbursed_by' => $this->staff_id,
                    'created_at' => now(),
                ]);
            } else {
                // Update existing packing slip
                $packingSlip->update([
                    'customer_id' => $this->customer_id,
                    'is_disbursed' => 0,
                    'disbursed_by' => $this->staff_id,
                    'updated_at' => now(),
                ]);
            }

            // 2. Check if invoice already exists
            $invoice = Invoice::where('order_id', $order->id)->first();

            if (!$invoice) {
                do {
                    $lastInvoice = Invoice::orderBy('id', 'DESC')->first();
                    $invoice_no = str_pad(optional($lastInvoice)->id + 1, 6, '0', STR_PAD_LEFT);
                } while (Invoice::where('invoice_no', $invoice_no)->exists());

                $order->invoice_type = $this->document_type;

                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->staff_id,
                    'packingslip_id' => $packingSlip->id,
                    'invoice_no' => $invoice_no,
                    'net_price' => $order->total_amount,
                    'required_payment_amount' => $order->total_amount,
                    'created_by' => $this->staff_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Update existing invoice
                $invoice->update([
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->staff_id,
                    'packingslip_id' => $packingSlip->id,
                    'net_price' => $order->total_amount,
                    'required_payment_amount' => $order->total_amount,
                    'updated_at' => now(),
                ]);
            }

            // 3. Update invoice products
            InvoiceProduct::where('invoice_id', $invoice->id)->delete();

            $orderItems = $order->items;

            foreach ($orderItems as $item) {
                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'order_item_id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : '',
                    'quantity' => $item->quantity,
                    'single_product_price' => $item->piece_price,
                    'total_price' => $item->total_price + ($item->air_mail ?? 0),
                    'is_store_address_outstation' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Update or create ledger entry
            Ledger::updateOrCreate(
                [
                    'user_type' => 'customer',
                    'transaction_id' => $invoice->invoice_no,
                    'customer_id' => $order->customer_id,
                    'purpose' => 'invoice',
                ],
                [
                    'transaction_amount' => $order->total_amount,
                    'bank_cash' => 'cash',
                    'is_credit' => 0,
                    'is_debit' => 1,
                    'entry_date' => now(),
                    'purpose_description' => 'invoice raised of sales order for customer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }


    public function is_valid_date($date) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return true;
        }
        return false;
    }
    public function ResetForm(){
        $this->reset(['customer','customer_id','staff_id', 'amount', 'voucher_no', 'payment_date', 'payment_mode', 'chq_utr_no', 'bank_name']);
        $this->voucher_no = 'PAYRECEIPT'.time();
    }
    public function ChangePaymentMode($value){
        $this->activePayementMode = $value;
    }
    public function render()
    {

        // Fetch the order and its related items
       $order = Order::with([
            'items.deliveries' => function ($q) {
                $q->with('user:id,name');
            },
            'items.voice_remark',
            'items.catlogue_image'
        ])->findOrFail($this->orderId);
        
         $orderItems = $order->items->map(function ($item) use($order) {
         $product = \App\Models\Product::find($item->product_id);
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
                'product_name' => $item->product_name ?? $product->name,
                'collection_id' => $item->collection,
                'collection_title' => $item->collectionType ?  $item->collectionType->title : "",
                'fabrics' => $item->fabric,
                'measurements' => $measurements,
                'catalogue' => $item->catalogue_id?$item->catalogue:"",
                'catalogue_id' => $item->catalogue_id,
                'cat_page_number' => $item->cat_page_number,
                'cat_page_item' => $item->cat_page_item,
                'piece_price' => $item->piece_price,
                // 'deliveries' => !empty($item->deliveries)?
                //     $item->deliveries:"",
                'deliveries' => !empty($item->deliveries)
                    ? $item->deliveries->map(function ($delivery) use ($item) {
                        return [
                            'id' => $delivery->id,
                            'delivered_at' => $delivery->delivered_at,
                            'status' => $delivery->status,
                            'remarks' => $delivery->remarks,
                            'fabric_quantity' => $delivery->fabric_quantity,
                            'delivered_quantity' => $delivery->delivered_quantity,
                            'user' => $delivery->user ? ['name' => $delivery->user->name] : ['name' => 'N/A'],
                            'collection_id' => $item->collection, // inject here for later use
                        ];
                    })
                    : [],
                'quantity' => $item->quantity,
                'remarks' => $item->remarks,
                'catlogue_images' => $item->catlogue_image,
                'voice_remarks' => $item->voice_remark,

                'product_image' => $product ? $product->product_image : null,
                'expected_delivery_date' => $item->expected_delivery_date,
                'fittings' => $item->fittings,
                'priority' => $item->priority_level,
                'status'  => $item->status,
                'tl_status'  => $item->tl_status,
                'admin_status'  => $item->admin_status,

                // Extra fields packed here
                'extra_type' => $extra,
                'mens_hand_stitching'   => $item->mens_hand_stitching,
                'ladies_hand_stitching' => $item->ladies_hand_stitching,
                'shoulder_type' => $item->shoulder_type,
                'vents' => $item->vents,
                'vents_required' => $item->vents_required,
                'vents_count' => $item->vents_count,
                'fold_cuff_required' => $item->fold_cuff_required,
                'fold_cuff_size' => $item->fold_cuff_size,
                'pleats_required' => $item->pleats_required,
                'pleats_count' => $item->pleats_count,
                'back_pocket_required' => $item->back_pocket_required,
                'back_pocket_count' => $item->back_pocket_count,
                'adjustable_belt' => $item->adjustable_belt,
                'suspender_button' => $item->suspender_button,
                'trouser_position' => $item->trouser_position,
                'client_name_required' => $item->client_name_required,
                'client_name_place' => $item->client_name_place,
                'client_name_options'     => $item->client_name_options,   

            ];
        });

       $customer_deposits=[];
       $customer_netdue=[];
            // Fetch invoices with filters
            $invoices = Invoice::where('customer_id',$order->customer_id)
                                ->where('order_id','!=',$order->id)
                                ->with('customer:id,name')
                                ->orderBy('created_at', 'desc')

           ->get()->map(function ($item) use (&$customer_deposits,&$customer_netdue) {
                    if (!array_key_exists($item->customer_id, $customer_deposits))
                    {
                        $total_amount = Order::whereHas('invoice')
                                        ->where('customer_id', $item->customer_id)
                                        ->sum('total_amount');
                        $total_deposit = PaymentCollection::where('customer_id', $item->customer_id)->sum('collection_amount');
                        $net_due = $total_amount-$total_deposit;
                        $customer_netdue[$item->customer_id]=$net_due;
                        $customer_deposits[$item->customer_id]=$total_deposit;

                    }

                    if($customer_netdue[$item->customer_id]<=0)
                    {
                        $item->due_amnt=0;
                    }
                    else{
                        if($customer_deposits[$item->customer_id]>$item->net_price)
                        {
                            $item->due_amnt=0;
                            $customer_deposits[$item->customer_id]=$customer_deposits[$item->customer_id]-$item->net_price;
                        }
                        else{
                            if($customer_deposits[$item->customer_id]>0)
                            {
                                 $item->due_amnt=$item->net_price-$customer_deposits[$item->customer_id];
                                 $customer_deposits[$item->customer_id]=0;
                            }
                            else{
                                $item->due_amnt=$item->net_price;
                            }
                        }
                    }


                    return $item;
            })
            ->filter(function ($item) {return $item->due_amnt > 0;})
            ->values()
            ->slice(0, 2) 
           ;

       $customer_deposits=[];
       $customer_netdue=[];
            // Fetch invoices with filters
            $rest_invoices = Invoice::where('customer_id',$order->customer_id)
                                    ->where('order_id','!=',$order->id)
                                    ->with('customer:id,name')
                                    ->orderBy('created_at', 'desc')
            
           ->get()->map(function ($item) use (&$customer_deposits,&$customer_netdue) {
                    if (!array_key_exists($item->customer_id, $customer_deposits))
                    {
                        $total_amount = Order::whereHas('invoice')
                                        ->where('customer_id', $item->customer_id)
                                        ->sum('total_amount');
                        $total_deposit = PaymentCollection::where('customer_id', $item->customer_id)->sum('collection_amount');
                        $net_due=$total_amount-$total_deposit;
                        $customer_netdue[$item->customer_id]=$net_due;
                        $customer_deposits[$item->customer_id]=$total_deposit;

                    }

                    if($customer_netdue[$item->customer_id]<=0)
                    {
                        $item->due_amnt=0;
                    }
                    else{
                        if($customer_deposits[$item->customer_id]>$item->net_price)
                        {
                            $item->due_amnt=0;
                            $customer_deposits[$item->customer_id]=$customer_deposits[$item->customer_id]-$item->net_price;
                        }
                        else{
                            if($customer_deposits[$item->customer_id]>0)
                            {
                                 $item->due_amnt=$item->net_price-$customer_deposits[$item->customer_id];
                                 $customer_deposits[$item->customer_id]=0;
                            }
                            else{
                                $item->due_amnt=$item->net_price;
                            }
                        }
                    }


                    return $item;
            })
            ->filter(function ($item) {return $item->due_amnt > 0;})
            ->values()
            ->slice(2, 8) 
           ;


        return view('livewire.order.add-order-slip',[
            'order_detail' => $order,
            'invoices'=>$invoices,
            'rest_invoices'=>$rest_invoices,
            'orderItemsNew' => $orderItems,
        ]);
    }
}
