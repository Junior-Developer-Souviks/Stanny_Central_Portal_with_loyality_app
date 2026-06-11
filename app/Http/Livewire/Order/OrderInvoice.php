<?php

namespace App\Http\Livewire\Order;
use App\Models\Order;
use Livewire\Component;

class OrderInvoice extends Component
{
    public $order;

    public function mount($id)
    {
        $this->order = Order::with('items.collectionType')->findOrFail($id);
        // dd($this->order); // Fetch the order by ID
    }

    public function render()
    {
        return view('livewire.order.order-invoice', [
            'order' => $this->order
        ]);
    }
}
