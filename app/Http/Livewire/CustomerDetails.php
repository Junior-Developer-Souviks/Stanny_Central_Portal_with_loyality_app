<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class CustomerDetails extends Component
{
    public $customerId; 
    public $customer;  
    public $latestOrders; 

    public function mount($id)
    {
        $this->customerId = $id;

        $this->customer = User::with(['billingAddressLatest', 'shippingAddressLatest','ordersAsCustomer'])->findOrFail($this->customerId);
        $this->latestOrders = $this->customer->ordersAsCustomer()->orderBy('created_at', 'desc')->take(10)->get();

        // dd($this->customer);
    }

    
    public function redirectToOrderPage()
    {
        return redirect()->route('admin.order.new', ['user_id' => $this->customerId]);
    }

    public function redirectToOrderIndex(){
        return redirect()->route('admin.order.index', ['customer_id' => $this->customerId]);
    }
    

    public function render()
    {
        return view('livewire.customer-details');
    }
}
