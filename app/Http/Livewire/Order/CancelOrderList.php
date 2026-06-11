<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class CancelOrderList extends Component
{
    use WithPagination;
    
    public $customer_id;
    public $created_by, $search,$status,$start_date,$end_date; 
    public $invoiceId;
    public $orderId;


    protected $paginationTheme = 'bootstrap'; // Optional: For Bootstrap styling
    
   
    public function render()
    {
        $auth = Auth::guard('admin')->user(); // Get authenticated user
        $placed_by = User::where('user_type', 0)->get(); // Fetch users who placed orders
    
        // Fetch won orders based on authentication
        $wonOrders = $auth->is_super_admin
            ? Order::pluck('created_by')->toArray()
            : $auth->orders()->where('created_by', $auth->id)->pluck('created_by')->toArray();
    
        $this->usersWithOrders = $wonOrders;
    
        // Fetch Canceled Orders
        $canceledOrders = Order::query()
            ->where('status', 'Cancelled')
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', fn($q) =>
                          $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%')
                      );
            })
            ->when($this->created_by, fn($query) => $query->where('created_by', $this->created_by))
            ->when($this->start_date, fn($query) => $query->whereDate('created_at', '>=', $this->start_date))
            ->when($this->end_date, fn($query) => $query->whereDate('created_at', '<=', $this->end_date))
            ->when(!$auth->is_super_admin, fn($query) => $query->where('created_by', $auth->id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.order.cancel-order-list', [
            'orders' => $canceledOrders,
            'usersWithOrders' => $this->usersWithOrders, 
        ]);
    }
   
}
