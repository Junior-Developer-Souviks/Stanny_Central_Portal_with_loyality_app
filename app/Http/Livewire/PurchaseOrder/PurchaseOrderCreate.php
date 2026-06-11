<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Collection;
use App\Models\Fabric;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use Illuminate\Support\Facades\DB;

class PurchaseOrderCreate extends Component
{
    public $suppliers,$collections,$fabrics = [];
    public $selectedCollection = null,$product = null;
    public $isFabricSelected =  [];
    public $rows = []; 
    public $selectedSupplier = null;

    public function mount(){
        $this->suppliers = Supplier::where('status',1)->where('deleted_at',NULL)->get();
        $this->collections = Collection::all()->toArray();
        $this->fabrics = [];
        $this->product = [];
        $this->rows = [
            ['collection' => null, 'fabric' => [], 'product' => [], 'pcs_per_mtr' => 1, 'pcs_per_qty' => 1, 'price_per_pc' => null, 'total_amount' => null],
        ];
    }

    public function SelectedSupplier($value){
        $this->selectedCollection = null;
        $this->product = [];
        $this->fabrics = [];
        $this->isFabricSelected = false;
    }

    // Total Amount Calculated
    public function updateRowAmount($index)
    {
        // Check if the row exists
        if (!isset($this->rows[$index])) {
            return;
        }
    
        // Fetch row values
        $row = $this->rows[$index];
        $pcsPerMtr = $row['pcs_per_mtr'] ?? 0; 
        $pcsPerQty = $row['pcs_per_qty'] ?? 0; 
        $pricePerMtr = $row['price_per_mtr'] ?? 0;
        $pricePerQty = $row['price_per_qty'] ?? 0;

    
        // Calculate total amount based on input values
        if($this->isFabricSelected[$index] ?? false){
            // Calculate total amount based on pcs_per_mtr and price_per_pc for Garments
            if($pcsPerMtr > 0 && $pricePerMtr > 0){
                $this->rows[$index]['total_amount'] = $pcsPerMtr * $pricePerMtr;
            } else {
                $this->rows[$index]['total_amount'] = null; // Reset if invalid inputs
            }
        } else {
            // Calculate total amount based on pcs_per_qty and price_per_pc for Garment Items
            if($pcsPerQty > 0 && $pricePerQty > 0){
                $this->rows[$index]['total_amount'] = $pcsPerQty * $pricePerQty;
            } else {
                $this->rows[$index]['total_amount'] = null; // Reset if invalid inputs
            }
        }
    }
    

    // PurchaseOrder Create
    public function savePurchaseOrder()
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
            // Begin database transaction
            DB::beginTransaction();
            $supplier = Supplier::find($this->selectedSupplier);
            $designationId = auth()->guard('admin')->user()->id;
            // Insert the purchase order
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->supplier_id = $this->selectedSupplier;
            $purchaseOrder->unique_id = 'PO' . time();
            $purchaseOrder->address = $supplier->billing_address;
            $purchaseOrder->city = $supplier->billing_city;
            $purchaseOrder->pin = $supplier->billing_pin;
            $purchaseOrder->state = $supplier->billing_state;
            $purchaseOrder->country = $supplier->billing_country;
            $purchaseOrder->landmark = $supplier->billing_landmark;
            $purchaseOrder->goods_in_type = "bulk";
            $purchaseOrder->total_price = array_sum(array_column($this->rows, 'total_amount'));
            $productIds = [];
            $fabricIds = [];
            $purchaseOrder->is_approved = ($designationId == 1) ? 1 : 0;
            $purchaseOrder->created_by = $designationId ?? null;
            $purchaseOrder->save();

            // Insert related purchase order products
            foreach ($this->rows as $index => $row) {
                $purchaseOrderProduct = new PurchaseOrderProduct();
                $purchaseOrderProduct->purchase_order_id = $purchaseOrder->id;
                $purchaseOrderProduct->collection_id = $row['collections'];
                // Check if fabric is selected and fetch fabric_name
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
                $purchaseOrderProduct->piece_price =$this->isFabricSelected[$index] ? $row['price_per_mtr'] : $row['price_per_qty'];
                $purchaseOrderProduct->total_price = $row['total_amount'];
                $purchaseOrderProduct->fabric_id = $row['fabric'] ?? null;
                // $purchaseOrderProduct->qty_in_meter = $row['pcs_per_mtr'] ?? null;
                $purchaseOrderProduct->product_id = $row['product'] ?? null;
                // Check if product is selected and fetch product name
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
                // $purchaseOrderProduct->qty_in_pieces = $row['pcs_per_qty'] ?? null;
                $purchaseOrderProduct->save();
            }

              // Save product_ids and fabric_ids as comma-separated strings in purchase_order table
             $purchaseOrder->product_ids = !empty($productIds) ? json_encode($productIds) : json_encode([]);
            $purchaseOrder->fabric_ids = !empty($fabricIds) ? json_encode($fabricIds) : json_encode([]);
            $purchaseOrder->save();
            // Commit transaction
            DB::commit();
    
            session()->flash('success', 'Purchase order created successfully!');
            return redirect()->route('purchase_order.index');
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();
            session()->flash('error', 'Something went wrong: ' . $e->getMessage());
            dd($e->getMessage());
        }
        
    }
    

    public function SelectedCollection($index, $collectionId)
    {
        $this->rows[$index]['collection'] = $collectionId;
        $collection = Collection::find($collectionId);

        if ($collection) {
            if ($collection->id === 1) { // GARMENT
                $allFabrics = Fabric::where('status', 1)->get()->toArray();
                // Exclude fabrics already selected in other rows
                $selectedFabrics = array_column(array_filter($this->rows, function ($row) use ($index) {
                    return $row['collection'] == 1 && $row['fabric'] != null && $row !== $this->rows[$index];
                }), 'fabric');

                $filteredFabrics = array_filter($allFabrics, function ($fabric) use ($selectedFabrics) {
                    return !in_array($fabric['id'], $selectedFabrics);
                });
                $this->rows[$index]['fabrics']  = array_values($filteredFabrics);
                $this->rows[$index]['products'] = [];
                $this->rows[$index]['fabric'] = null;
                $this->rows[$index]['product'] = null;
                $this->isFabricSelected[$index] = true;
            } elseif (in_array($collection->id, [2, 4])) { // GARMENT ITEMS
                $allProducts = Product::where('collection_id', $collection->id)->where('status',1)->where('deleted_at',NULL)->get()->toArray();
                // Exclude products already selected in other rows
                $selectedProducts = array_column(array_filter($this->rows , function ($row) use ($index){
                    return in_array($row['collection'], [2, 4]) && $row['product'] != null && $row !== $this->rows[$index];
                }),'product');

                $filteredProducts = array_filter($allProducts, function ($product) use ($selectedProducts) {
                    return !in_array($product['id'], $selectedProducts);
                });

                $this->rows[$index]['products'] = array_values($filteredProducts);
                $this->rows[$index]['fabrics'] = [];
                $this->rows[$index]['fabric'] = null;
                $this->rows[$index]['product'] = null;
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

    public function addRow()
    {
        $this->rows[] = ['collection' => null, 'fabric' => [], 'product' => [], 'pcs_per_mtr' => 1, 'pcs_per_qty' => 1, 'price_per_mtr' => null, 'price_per_qty' => null, 'total_amount' => null];

    }

    public function removeRow($index){
        if (!is_array($this->rows)) {
             $this->rows = [];
        }
        if (!is_array($this->isFabricSelected)) {
            $this->isFabricSelected = [];
        }

        unset($this->rows[$index]);
        unset($this->isFabricSelected[$index]);
        $this->rows = array_values($this->rows);
        $this->isFabricSelected = array_values($this->isFabricSelected);
    }

    public function resetForm()
    {
        // Reset all form data
        $this->selectedSupplier = null;
        $this->rows = [
            ['collection' => null, 'fabric' => [], 'product' => [], 'pcs_per_mtr' => 1, 'pcs_per_qty' => 1, 'price_per_pc' => null, 'total_amount' => null],
        ];
        $this->isFabricSelected = [];
        $this->selectedCollection = null;
        $this->fabrics = [];
        $this->product = [];
        $this->collections = Collection::all()->toArray(); // Optionally re-fetch collections or data that should be reset
    }

    public function resetItems()
    {
        // Reset only the rows (items) data
        $this->rows = [
            ['collection' => null, 'fabric' => [], 'product' => [], 'pcs_per_mtr' => 1, 'pcs_per_qty' => 1, 'price_per_pc' => null, 'total_amount' => null],
        ];
        $this->isFabricSelected = [];
    }



    
    public function render()
    {
        return view('livewire.purchase-order.purchase-order-create');
    }
}
