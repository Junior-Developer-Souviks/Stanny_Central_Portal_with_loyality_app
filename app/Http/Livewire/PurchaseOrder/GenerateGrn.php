<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Helpers\Helper;
use App\Models\Stock;
use App\Models\StockProduct;
use App\Models\StockFabric;
use App\Models\Ledger;


class GenerateGrn extends Component
{
    public $purchaseOrderId;
    public $purchaseOrder;
    public $fabricUniqueNumbers = [];
    public $productUniqueNumbers = [];

    // Product
    public $selectedBulkIn = []; 
    public $selectedUniqueNumbers = [];
    // Fabric
    public $selectedFabricBulkIn=[];
    public $selectedFabricUniqueNumbers=[];
    // Generate Grn
    public $productTotalPrice = 0;
    public $fabricTotalPrice = 0;
    public $totalPrice = 0;
    // grn quantity
    public $grnQuantities = [];
    public $prices = [];
    public $selectAll = false;



    public function mount($purchase_order_id){
         $this->purchaseOrderId = $purchase_order_id;
         $this->purchaseOrder = PurchaseOrder::with('orderproducts.product', 'orderproducts.fabric','orderproducts.collection')->find($this->purchaseOrderId);    
        // Pre-fill GRN quantity
        foreach ($this->purchaseOrder->orderproducts  as $orderProduct) {
            $this->grnQuantities[$orderProduct->id] = $orderProduct->collection_id == 1 
            ? $orderProduct->qty_in_meter 
            : $orderProduct->qty_in_pieces;

            $this->prices[$orderProduct->id] = $orderProduct->total_price;
        }  
    }

    public function toggleAllCheckboxes(){
        if($this->selectAll){
            // select all fabrics
            $this->selectedFabricBulkIn = $this->purchaseOrder->orderproducts->where('collection_id',1)->pluck('id')->toArray();
            // select all products
            $this->selectedBulkIn = $this->purchaseOrder->orderproducts->where('collection_id','!=',1)->pluck('id')->toArray();
        }else{
            $this->selectedFabricBulkIn = [];
            $this->selectedBulkIn = [];
        }
    }

    public function incrementGrnQuantity($orderProductId){
        $product = $this->purchaseOrder->orderproducts->firstwhere('id',$orderProductId);
        if($product){
            $maxQuantity = $product->collection_id == 1 ? intval($product->qty_in_meter) : intval($product->qty_in_pieces);
             // Prevent exceeding the maximum allowed quantity
            if(!isset($this->grnQuantities[$orderProductId]) || ($this->grnQuantities[$orderProductId] < $maxQuantity)){
                $this->grnQuantities[$orderProductId] = isset($this->grnQuantities[$orderProductId]) ? $this->grnQuantities[$orderProductId] + 1 : 1;
                $this->updatePrice($orderProductId);
            }else{
                session()->flash('error','GRN quantity cannot exceed the order quantity.');
            }
        }

    }

    public function decrementGrnQuantity($orderProductId){
        if($this->grnQuantities[$orderProductId] && $this->grnQuantities[$orderProductId] > 0){
            $this->grnQuantities[$orderProductId] -=1;
            $this->updatePrice($orderProductId);
        }
    }

    public function updatePrice($orderProductId){
        $product = $this->purchaseOrder->orderproducts->firstWhere('id',$orderProductId);
        if($product){
            $this->prices[$orderProductId] = $product->piece_price * ($this->grnQuantities[$orderProductId] ?? 0);
        }
    }

    // Generate GRN
    public function generateGrn()
    {
        try {
            $this->productTotalPrice = 0;
            $this->fabricTotalPrice = 0;
            $productIds = [];
            $fabricIds = [];

            $grn_no =  "GRN-" . Helper::generateUniqueNumber();
            $stocks = new Stock();
            $stocks->grn_no = $grn_no;
            $stocks->purchase_order_id = $this->purchaseOrderId;
            $stocks->po_unique_id = $this->purchaseOrder->unique_id;
            $stocks->goods_in_type = 'goods_in';

            $stocks->save();

            // Insert Stock Products
            if(!empty($this->selectedBulkIn)){
                foreach ($this->selectedBulkIn as $orderProductId) {
                    $product = $this->purchaseOrder->orderproducts->find($orderProductId);
                    if($product){
                        $grnQty = $this->grnQuantities[$orderProductId] ?? 0; // Use GRN Quantity
                        $productTotalPrice = $grnQty * $product->piece_price;
                        $stockProduct = new StockProduct();
                        $stockProduct->stock_id = $stocks->id;
                        $stockProduct->product_id = $product->product->id;
                        $stockProduct->qty_in_pieces = $product->qty_in_pieces;
                        $stockProduct->qty_while_grn = $grnQty;
                        $stockProduct->piece_price = $product->piece_price;
                        $stockProduct->total_price = $productTotalPrice;
                        $stockProduct->save();
                        // update purchase order product table 
                        $product->qty_while_grn_product = $grnQty;
                        $product->total_price = $productTotalPrice;
                        $product->save();

                        $productIds[] = $product->product->id;
                        $this->productTotalPrice += $productTotalPrice;
                    }
                }
            }

            // Insert Stock Fabrics
            if(!empty($this->selectedFabricBulkIn))
                foreach ($this->selectedFabricBulkIn as $orderProductId) {
                    $fabric = $this->purchaseOrder->orderproducts->find($orderProductId);
                    if($fabric){
                        $grnQty = $this->grnQuantities[$orderProductId] ?? 0;
                        $fabricTotalPrice = $grnQty * $fabric->piece_price;
                        $stockFabric = new StockFabric();
                        $stockFabric->stock_id = $stocks->id;
                        $stockFabric->fabric_id = $fabric->fabric->id;
                        $stockFabric->qty_in_meter = $fabric->qty_in_meter;
                        $stockFabric->qty_while_grn = $grnQty;
                        $stockFabric->piece_price = $fabric->piece_price;
                        $stockFabric->total_price = $fabricTotalPrice;
                        $stockFabric->save(); 

                        // Update purchase order product table in case of fabric
                        $fabric->qty_while_grn_fabric = $grnQty;
                        $fabric->total_price = $fabricTotalPrice;
                        $fabric->save();

                        $fabricIds[] = $fabric->fabric->id;
                        $this->fabricTotalPrice += $fabricTotalPrice;
                    }
                }
               $stocks->product_ids = !empty($productIds) ? json_encode(array_unique($productIds)) : json_encode([]);
               $stocks->fabric_ids = !empty($fabricIds) ? json_encode(array_unique($fabricIds)) : json_encode([]);
                $stocks->total_price = $this->fabricTotalPrice + $this->productTotalPrice;
                $stocks->save();
                $this->purchaseOrder->total_price =  $this->fabricTotalPrice + $this->productTotalPrice;
                $this->purchaseOrder->status = 1;
                $this->purchaseOrder->save();

                //  Supplier Ledger Entry
                Ledger::insert([
                    'user_type'=> 'supplier',
                    'supplier_id'=> $this->purchaseOrder->supplier_id,
                    'transaction_id'=> $grn_no,
                    'transaction_amount' => $stocks->total_price,
                    'entry_date'=> date('Y-m-d'),
                    'is_credit'=> 1,
                    'purpose'=> 'goods_received_note',
                    'purpose_description' => 'Goods Received Note',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                session()->flash('success', 'GRN Generated Successfully');
                return redirect()->route('purchase_order.index');
           
        } catch (\Exception $e) {
            // Log the error and flash a user-friendly message
            \Log::error('Error generating GRN: ' . $e->getMessage());
            dd($e->getMessage());
            session()->flash('error', 'An error occurred while generating the GRN. Please try again.');
            return redirect()->back();
        }
    }
    
    public function render()
    {
        return view('livewire.purchase-order.generate-grn');
    }
}
