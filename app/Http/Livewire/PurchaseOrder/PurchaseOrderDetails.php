<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use App\Models\PurchaseOrder;

class PurchaseOrderDetails extends Component
{   
    public $purchaseOrder;
    public $activeTab = 'fabric';  //Default Active tab

    public function mount($purchase_order_id){
        $this->purchaseOrder  = PurchaseOrder::with('orderproducts.product', 'orderproducts.fabric','orderproducts.collection')->find($purchase_order_id);    
        // dd($this->purchase_order_id);
    }

    public function setActiveTab($tab){
        $this->activeTab = $tab;
    }
    public function render()
    {
        return view('livewire.purchase-order.purchase-order-details');
    }
}
