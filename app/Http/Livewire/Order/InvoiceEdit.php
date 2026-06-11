<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\InvoiceProduct;

class InvoiceEdit extends Component
{
    public $id;
    public $invoice;
    public $invoice_products;
    public function mount($id){
        $this->id = $id;
        $this->invoice = Invoice::find($this->id);
        $this->invoice_products = InvoiceProduct::where('invoice_id',$this->invoice->id)->get();
    }
    public function render()
    {
        return view('livewire.order.invoice-edit');
    }
}
