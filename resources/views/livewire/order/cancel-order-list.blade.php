<div class="container">
    <section class="admin__title">
        <h5>Cancel Order History</h5>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        
                        
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{count($orders)}} Items</p>
                </div>
               
            </div>
        </div>
    </section>
    <div class="card my-2">
        <div class="card-header pb-0">
            <div class="row">
                @if(session()->has('message'))
                    <div class="alert alert-success" id="flashMessage">
                        {{ session('message') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
            <div class="table-responsive p-0">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Order #</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Customer Details</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Order Amount</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Placed By</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="align-center">
                                    <span class="text-dark text-sm font-weight-bold mb-0">{{ env('ORDER_PREFIX'). $order->order_number }}</span><br>
                                    <p class="small text-muted mb-1 badge bg-warning">{{ $order->created_at->format('Y-m-d H:i') }}</p>
                                </td>
                                <td>
                                    <p class="small text-muted mb-1">
                                        <span>Name: <strong>{{$order->customer_name}}</strong> </span>
                                        <br>
                                        <span>Mobile : <strong>{{$order->customer?$order->customer->phone:""}}</strong> </span> <br>
                                        <span>WhatsApp : <strong>{{$order->customer?$order->customer->whatsapp_no:""}}</strong> </span>
                                    </p>
                                </td>
                                <td><p class="text-xs font-weight-bold mb-0">{{ $order->total_amount }}</p></td>
                                <td>
                                   <p class="small text-muted mb-1 text-uppercase">{{$order->createdBy?$order->createdBy->name:""}}</p>
                                </td>
                                {{-- <td class="{{$order->remaining_amount>0?"text-danger":""}}"><p class="text-xs font-weight-bold mb-0">{{ $order->remaining_amount }}</p></td> --}}
                                <td>
                                    <span class="badge bg-{{ $order->status_class }}">{{ $order->status_label }}</span>
                                </td>
                            <td class="text-center">
                                   
                                      
                                   
                                    <a href="{{route('admin.order.view',$order->id)}}" class="btn btn-outline-success select-md btn_action btn_outline">Details</a>

                                     
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
    @if(empty($search))
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
    @endif
    
</div>
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('confirmCancel', function(event) {
            console.log("Received confirmCancel Event:", event.detail);

            if (event.detail && event.detail.orderId) {
                console.log("Order ID from Event:", event.detail.orderId);
            } else {
                console.error("Order ID is missing in the event.");
                return;
            }

            if (confirm('Are you sure you want to cancel the order?')) {
                console.log("Dispatching cancelOrder event with Order ID:", event.detail.orderId);
                Livewire.dispatch('cancelOrder', { orderId: event.detail.orderId });
            }
        });
    });


    </script>
@endpush
