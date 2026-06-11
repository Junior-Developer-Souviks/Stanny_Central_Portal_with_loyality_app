<?php

namespace App\Http\Livewire\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Fabric;
use App\Models\PurchaseOrderProduct;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseOrderEdit extends Component
{
    public $purchase_order_id;
    public $purchaseOrder,$selectedSupplier,$suppliers; 
    public $isFabricSelected = [];
    public $selectedCollection = null, $product = null;
    public $rows = [];
    public $fabrics = [];
    public $collections;
    public function mount($purchase_order_id)
    {
        // Store the purchase order in a property
        $this->purchase_order_id = $purchase_order_id;
        $this->purchaseOrder = PurchaseOrder::with('orderproducts.collection','orderproducts.product', 'orderproducts.fabric',)->findOrFail($purchase_order_id);
        $this->collections = Collection::select('id','title')->get()->toArray();
        // Set default selected supplier
        $this->selectedSupplier = $this->purchaseOrder->supplier_id;
        $this->suppliers = Supplier::where('status',1)->where('deleted_at',NULL)->get();
        // Set rows for items
        $this->rows = [];
        foreach($this->purchaseOrder->orderproducts as $item) {
            $this->rows[] = [
                'collections' => $item->collection_id,
                'collection_name' => optional($item->collection)->title,
                'fabric' => $item->fabric_id,
                'product' => $item->product_id,
                'pcs_per_mtr' => $item->qty_in_meter,
                'pcs_per_qty' => $item->qty_in_pieces,
                'price_per_mtr' => $item->fabric_id ? $item->piece_price : null,
                'price_per_qty' => $item->product_id ? $item->piece_price : null,
                'total_amount' => $item->total_price,
                'fabrics' => $item->fabric ? [$item->fabric] : [],
                'products' => $item->product ? [$item->product] : [],
            ];
        }

        foreach($this->rows as $index => $row){
            $this->isFabricSelected[$index] = !empty($row['fabric']);
        }
        
    }

    public function SelectedSupplier($value)
    {
        $this->selectedSupplier = $value; 
        $supplier = Supplier::find($value);
    
        if ($supplier) {
            $this->purchaseOrder->address = $supplier->billing_address;
            $this->purchaseOrder->city = $supplier->billing_city;
            $this->purchaseOrder->pin = $supplier->billing_pin;
            $this->purchaseOrder->state = $supplier->billing_state;
            $this->purchaseOrder->country = $supplier->billing_country;
            $this->purchaseOrder->landmark = $supplier->billing_landmark;
        }

    }
    

    public function SelectedCollection($index, $collectionId)
    {
        $this->rows[$index]['collections'] = $collectionId;
        $collection = Collection::find($collectionId);

        if ($collection) {
            if ($collection->id === 1) { // GARMENT
                $allFabrics = Fabric::where('status', 1)->get()->toArray();
                $selectedFabrics = array_column(array_filter($this->rows, function ($row) use ($index) {
                    return $row['collections'] == 1 && $row['fabric'] != null && $row !== $this->rows[$index];
                }), 'fabric');

                $filteredFabrics = array_filter($allFabrics, function ($fabric) use ($selectedFabrics) {
                    return !in_array($fabric['id'], $selectedFabrics);
                });
                $this->rows[$index]['fabrics'] = array_values($filteredFabrics);
                $this->rows[$index]['products'] = [];
                $this->rows[$index]['fabric'] = null;
                $this->rows[$index]['product'] = null;
                $this->isFabricSelected[$index] = true;
            } elseif (in_array($collection->id, [2, 4])) { // GARMENT ITEMS
                $this->rows[$index]['products'] = Product::where('collection_id', $collection->id)->where('status',1)->where('deleted_at',NULL)->get()->toArray();
                $this->rows[$index]['fabrics'] = [];
                $this->isFabricSelected[$index] = false;
            } else {
                $this->rows[$index]['fabrics'] = [];
                $this->rows[$index]['products'] = [];
                $this->isFabricSelected[$index] = false;
            }
        } else {
            $this->rows[$index]['fabrics'] = [];
            $this->rows[$index]['products'] = [];
            $this->isFabricSelected[$index] = false;
        }

        $this->rows[$index]['fabric'] = null;
        $this->rows[$index]['product'] = null;
    }

    public function updateRowAmount($index)
    {
        if (!isset($this->rows[$index])) {
            return;
        }

        $row = $this->rows[$index];
        $pcsPerMtr = $row['pcs_per_mtr'] ?? 0;
        $pcsPerQty = $row['pcs_per_qty'] ?? 0;
        $pricePerMtr = $row['price_per_mtr'] ?? 0;
        $pricePerQty = $row['price_per_qty'] ?? 0;


        if ($this->isFabricSelected[$index] ?? false) {
            if ($pcsPerMtr > 0 && $pricePerMtr > 0) {
                $this->rows[$index]['total_amount'] = $pcsPerMtr * $pricePerMtr;
            } else {
                $this->rows[$index]['total_amount'] = null;
            }
        } else {
            if ($pcsPerQty > 0 && $pricePerQty > 0) {
                $this->rows[$index]['total_amount'] = $pcsPerQty * $pricePerQty;
            } else {
                $this->rows[$index]['total_amount'] = null;
            }
        }
    }

    public function updatePurchaseOrder()
    {
        $this->validate([
            'selectedSupplier' => 'required',
            'rows.*.collections' => 'required',
            'rows.*.fabric' => 'required_if:rows.*.product,null',
            'rows.*.product' => 'required_if:rows.*.fabric,null',
            'rows.*.pcs_per_mtr' => 'nullable|numeric|min:1',
            'rows.*.pcs_per_qty' => 'nullable|numeric|min:1',
           'rows.*.price_per_mtr' => 'required_if:rows.*.fabric,!null|nullable|numeric|min:0',
            'rows.*.price_per_qty' => 'required_if:rows.*.product,!null |nullable|numeric|min:0',
            'rows.*.total_amount' => 'required|numeric|min:0',
        ], [
            'rows.*.collections.required' => 'The collection field is required.',
            'rows.*.fabric.required_if' => 'The fabric field is required.',
            'rows.*.product.required_if' => 'The product field is required.',
            'rows.*.price_per_mtr.required_if' => 'The price per meter is required for fabric items.',
            'rows.*.price_per_qty.required_if' => 'The price per quantity is required for product items.',
            'rows.*.total_amount.required' => 'The total amount is required.',
        ]);

        try {
            DB::beginTransaction();
            $supplier = Supplier::find($this->selectedSupplier);

            // Update the purchase order
            $this->purchaseOrder->supplier_id = $this->selectedSupplier;
            $this->purchaseOrder->address = $supplier->billing_address;
            $this->purchaseOrder->city = $supplier->billing_city;
            $this->purchaseOrder->pin = $supplier->billing_pin;
            $this->purchaseOrder->state = $supplier->billing_state;
            $this->purchaseOrder->country = $supplier->billing_country;
            $this->purchaseOrder->landmark = $supplier->billing_landmark;
            $this->purchaseOrder->total_price = array_sum(array_column($this->rows, 'total_amount'));
            $productIds = [];
            $fabricIds = [];
            $this->purchaseOrder->save();

            // Delete existing purchase order products
            PurchaseOrderProduct::where('purchase_order_id', $this->purchaseOrder->id)->delete();

            // Insert updated purchase order products
            foreach ($this->rows as $index => $row) {
                $purchaseOrderProduct = new PurchaseOrderProduct();
                $purchaseOrderProduct->purchase_order_id = $this->purchaseOrder->id;
                $purchaseOrderProduct->collection_id = $row['collections'];
                if ($row['fabric']) {
                    $fabric = Fabric::find($row['fabric']);
                    $purchaseOrderProduct->fabric_name = $fabric ? $fabric->title : null;
                    $purchaseOrderProduct->stock_type = 'fabric';
                    $fabricIds[] = $row['fabric'];
                    $purchaseOrderProduct->qty_in_meter = $row['pcs_per_mtr'] ?? null;
                } else {
                    $purchaseOrderProduct->fabric_name = null;
                    $purchaseOrderProduct->qty_in_meter = null;
                }
                $purchaseOrderProduct->piece_price = $this->isFabricSelected[$index] ? $row['price_per_mtr'] : $row['price_per_qty'];
                $purchaseOrderProduct->total_price = $row['total_amount'];
                $purchaseOrderProduct->fabric_id = $row['fabric'] ?? null;
                $purchaseOrderProduct->product_id = $row['product'] ?? null;
                if ($row['product']) {
                    $product = Product::find($row['product']);
                    $purchaseOrderProduct->product_name = $product ? $product->name : null;
                    $purchaseOrderProduct->stock_type = 'product';
                    $productIds[] = $row['product'];
                    $purchaseOrderProduct->qty_in_pieces = $row['pcs_per_qty'] ?? null;
                } else {
                    $purchaseOrderProduct->product_name = null;
                    $purchaseOrderProduct->qty_in_pieces = null;
                }
                $purchaseOrderProduct->save();
            }

            // Save product_ids and fabric_ids as comma-separated strings in purchase_order table
            $this->purchaseOrder->product_ids = implode(',', $productIds);
            $this->purchaseOrder->fabric_ids = implode(',', $fabricIds);
            $this->purchaseOrder->save();

            DB::commit();

            session()->flash('success', 'Purchase order updated successfully!');
            return redirect()->route('purchase_order.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong: ' . $e->getMessage());
            dd($e->getMessage());
        }
    }

    public function addRow(){
        $this->rows[] = [
            'collection'=>null,
            'fabric' => [], 
            'product' => [], 
            'pcs_per_mtr' => 1, 
            'pcs_per_qty' => 1, 
            'price_per_mtr' => null, 'price_per_qty' => null, 
            'total_amount' => null
        ];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        unset($this->isFabricSelected[$index]);
        $this->rows = array_values($this->rows);
        $this->isFabricSelected = array_values($this->isFabricSelected);
    }
    

    public function render()
    {
        // Return the view for the component
        return view('livewire.purchase-order.purchase-order-edit');
    }
}
