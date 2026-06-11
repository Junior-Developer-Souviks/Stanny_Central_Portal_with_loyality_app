<div class="container">
    <section class="admin__title">
        <h5>Order History</h5>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row gx-3 gy-3 align-items-center">
                        <div class="col-auto" style="margin-top: -27px;">
                            <label for="" class="date_lable">Start Date</label>
                            <input type="date" wire:model="start_date" wire:change="AddStartDate($event.target.value)"
                                class="form-control select-md bg-white" placeholder="Start Date">
                        </div>
                        <div class="col-auto" style="margin-top: -27px;">
                            <label for="" class="date_lable">End date</label>
                            <input type="date" wire:model="end_date" wire:change="AddEndDate($event.target.value)"
                                class="form-control select-md bg-white" placeholder="End Date">
                        </div>

                        <div class="col-auto" style="margin-top: -27px;">
                            <label for="" class="date_lable">Status</label>
                            <select class="form-control select-md bg-white" wire:model="status"
                                wire:change="setStatus($event.target.value)">
                                <option value="">Status</option>
                                <option value="Approval Pending from TL">Approval Pending from TL</option>
                                <option value="Approved">Approved</option>
                                <option value="Ready for Delivery">Ready for Delivery</option>
                                <option value="Partial Delivered By Production">Partial Delivered By Production</option>
                                <option value="Fully Delivered By Production">Fully Delivered By Production</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Returned">Returned</option>
                                <option value="Delivered to Customer">Delivered to Customer</option>
                                <option value="Partial Delivered to Customer">Partial Delivered to Customer</option>
                                <option value="Approved By TL">Approved By TL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{count($orders)}} Items</p>
                </div>

                <div class="col-auto">
                    <div class="row gx-2 gy-2 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input"
                                id="customer" placeholder="Search by customer detail or Order number" value=""
                                style="width: 350px;" wire:keyup="FindCustomer($event.target.value)">
                        </div>

                        <div class="col-auto mt-3">
                            <button type="button" wire:click="resetForm"
                                class="btn btn-outline-danger select-md">Clear</button>
                        </div>

                    </div>
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Order #
                            </th>

                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Placed By
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                            <th
                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 text-center">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td class="align-center">
                                <span class="text-dark text-sm font-weight-bold mb-0">{{ env('ORDER_PREFIX').
                                    $order->order_number }}</span><br>
                                <p class="small text-muted mb-1 badge bg-warning">{{ $order->created_at->format('Y-m-d
                                    H:i') }}</p>
                            </td>

                            <td>
                                <p class="small text-muted mb-1 text-uppercase">
                                    {{$order->createdBy?strtoupper($order->createdBy->name .'
                                    '.$order->createdBy->surname):""}}</p>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            @php
                                // Check if there are any items assigned to production and not yet received
                                  //      $hasPendingProductionItems = $order->items()
                                  //  ->where('assigned_team', 'production')
                                     //->where(function($q) {
                                    //        $q->whereNotNull('received_at');
                                    //    })
                                  //  ->exists(); --}}
                                    $hasPendingProductionItems = false;

                                   if (in_array($order->status, ['Partial Approved By Admin', 'Fully Approved By Admin'])) {

                                        $hasPendingProductionItems = $order->items()
                                            ->where('assigned_team', 'production')
                                            ->whereNotNull('received_at')
                                            ->exists();
                                    }


                            @endphp
                            <td class="text-center">
                                @if ($hasPendingProductionItems)
                                <button wire:click="confirmMarkAsReceived({{ $order->id }})"
                                    class="btn btn-outline-success select-md btn_outline" @click.stop>Mark As
                                    Received
                                </button>
                                
                                @elseif(in_array($order->status,[
                                                   'Received at Production',
                                                   'Partial Delivered By Production',
                                                   'Fully Delivered By Production',
                                                   'Partial Approved By Admin',
                                                   'Fully Approved By Admin'
                                        ]))
                                    {{-- 1. Check for the exception status first --}}
                                        @if ($order->status == 'Fully Delivered By Production')
                                            <button class="btn btn-outline-success select-md btn_action btn_outline" disabled>
                                                Delivered
                                            </button>
                                        
                                        {{-- 2. Everything else in the list gets the Stock Entry button --}}
                                        @else
                                            <a href="{{ route('production.order.details', $order->id) }}"
                                               class="btn btn-outline-success select-md btn_action btn_outline">
                                               Stock Entry
                                            </a>
                                        @endif
                                    
                                @endif
                                
                                @if (!in_array($order->status, [
                                    'Fully Approved By Admin',
                                    'Partial Approved By Admin',
                                    'Partial Delivered to Customer'
                                ]))
                                <a href="{{route('production.order.details',$order->id)}}"
                                    class="btn btn-outline-success select-md btn_action btn_outline">Details</a>
                                @endif
                                <a href="{{route('production.order.download_pdf',$order->id)}}" target="_blank"
                                    class="btn btn-outline-primary select-md btn_outline">
                                    Download Pdf
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>

                <!-- Stock Entry Modal -->
                <div wire:ignore.self class="modal fade" id="stockEntryModal" tabindex="-1"
                    aria-labelledby="stockEntryModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="stockEntryModalLabel">Enter Stock for Order
                                    #{{$stockOrderId}}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    wire:click="closeStockModal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Your stock entry form goes here -->
                                <div class="mb-3">
                                    <label for="stockItem" class="form-label">Fabric / Stock Item</label>
                                    <input type="text" id="stockItem" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity Used</label>
                                    <input type="number" id="quantity" class="form-control">
                                </div>
                                <!-- Add more fields as needed -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    wire:click="closeStockModal">Close</button>
                                <button type="button" class="btn btn-primary">Save Stock Entry</button>
                            </div>
                        </div>
                    </div>
                </div>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('showMarkAsReceived', function (event) {
           let orderId = event.detail[0].orderId;
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to mark this order as received.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Continue..',
                cancelButtonText: 'Print PDF'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Livewire.dispatch('markReceivedConfirmed', { orderId: data.orderId });
                    @this.call('markReceivedConfirmed', orderId); // Call Livewire method
                    Swal.fire("Mark As Received!", "The order has been marked as received.", "success");
                }  else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Print PDF using route helper
                    let pdfUrl = "{{ route('production.order.download_pdf', ':id') }}".replace(':id', orderId);
                    let printWindow = window.open(pdfUrl, '_blank');
                    printWindow.onload = function() {
                        printWindow.focus();
                        printWindow.print();
                         @this.call('markReceivedConfirmed', orderId); // Call Livewire method
                      Swal.fire("Mark As Received!", "The order has been marked as received.", "success");
                    };
                }
            });
        });
</script>