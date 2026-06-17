<div class="container">
    <section class="admin__title">
        <h5>Order History</h5>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto" style="margin-top: -27px;">
                            <label for="search" class="date_lable mb-1">
                                    Search
                            </label>
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input"
                                id="customer" placeholder="Search by customer detail or Order number" value=""
                                style="width: 350px;" wire:keyup="FindCustomer($event.target.value)">
                        </div>
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
                        @php
                           $auth = Auth::guard('admin')->user();
                        @endphp
                        <div class="col-auto" style="margin-top: -27px;">
                            <label for="" class="date_lable">Status</label>
                            <select class="form-control select-md bg-white"
                                wire:change="setStatus($event.target.value)">
                                <option value="">All Orders</option>
                                <option value="Partial Approved By Admin">Partial Approved By Admin</option>
                                <option value="Fully Approved By Admin">Fully Approved By Admin</option>
                                <option value="Ready for Delivery">Ready for Delivery</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="On Hold">On Hold</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Returned">Returned</option>
                                <option value="Received by Sales Team">Received by Sales Team</option>
                                <option value="Delivered to Customer">Delivered to Customer</option>
                                <option value="Partial Delivered to Customer">Partial Delivered to Customer</option>
                                <option value="Approval Pending from TL">Approval Pending from TL</option>
                                <option value="Received at Production">Received at Production</option>
                                <option value="Partial Delivered By Production">Partial Delivered By Production</option>
                                <option value="Fully Delivered By Production">Fully Delivered By Production</option>
                                <option value="Partial Approved By TL">Partial Approved By TL</option>
                                <option value="Fully Approved By TL">Fully Approved By TL</option>
                                
                                <option value="approval_pending_from_admin" {{ $status == 'approval_pending_from_admin' ? 'selected' : '' }}>Approval Pending from Admin</option>
                            </select>
                           
                        </div>
                        <div class="col-md-auto mt-3">
                            <a href="{{route('admin.order.new')}}" class="btn btn-outline-success select-md">Place New
                                Order</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{count($orders)}} Items</p>
                </div>
                 

                <div class="col-auto">
                    <div class="row g-3 align-items-end">
                      
                        <div class="col-auto mt-0" wire:ignore>
                            @if ($auth->is_super_admin)
                            <div class="d-flex flex-column">
                                 <label for="created_by" class="date_lable mb-1">
                                    Placed By
                                </label>
                                <select multiple  wire:model="created_by" class="form-control select-md bg-white"
                                    wire:change="CollectedBy($event.target.value)" id="created_by" style="width: 190px;">
                                    <option value="" hidden="" selected=""></option>
                                    @foreach($placed_by as $user)
                                    @if(in_array($user->id, $usersWithOrders))
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                                <a href="{{route('admin.order.index')}}" 
                                class="btn btn-outline-danger select-md">Clear</a>
                        </div>
                        <div class="col-auto">
                            <a href="javscript:void(0)" wire:click="export" class="btn btn-outline-success select-md"><i
                                    class="fas fa-file-csv me-1"></i>Export</a>
                        </div>
                        @if (!$auth->is_super_admin && $auth->user_type == 0) 
                        <div class="col-auto">
                            <a href="javascript:void(0)" wire:click="openImportModal" 
                                   class="btn btn-outline-success select-md mt-3">
                                    <i class="fas fa-file-csv me-1"></i>Import
                                </a>
                        </div>
                        @endif
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10" wire:click="sortBy('order_number')" style="cursor:pointer;">Order # 
                                @if($sortField === 'order_number')
                                   {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    ⇅
                                @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10" wire:click="sortBy('customer_name')" style="cursor:pointer;">Customer
                                Details
                              @if($sortField === 'customer_name')   
                                  {{$sortDirection === 'asc' ? '↑' : '↓' }}
                              @else
                                   ⇅
                              @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10" wire:click="sortBy('total_amount')" style="cursor:pointer;">Order
                                Amount
                                @if($sortField === 'customer_name')   
                                  {{$sortDirection === 'asc' ? '↑' : '↓' }}
                                  @else
                                       ⇅
                                @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10" wire:click="sortBy('created_by')" style="cursor:pointer;">Placed By
                                @if($sortField === 'created_by')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    ⇅
                                @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10" wire:click="sortBy('status')" style="cursor:pointer;">Status
                                @if($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    ⇅
                                @endif
                            </th>
                            <th
                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 text-center">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                             
                            <td class="align-center">
                                <span class="text-dark text-sm font-weight-bold mb-0">{{ config('app.order_prefix').
                                    $order->order_number }}</span><br>
                                <p class="small text-muted mb-1 badge bg-warning">{{ $order->created_at->format('Y-m-d
                                    H:i') }}</p>
                            </td>
                            <td>
                                <p class="small text-muted mb-1">
                                    <span>Name: <strong>{{ucwords($order->prefix ." ". $order->customer_name)}}</strong>
                                    </span>
                                    <br>
                                    <span>Mobile : <strong>{{$order->customer? $order->customer->country_code_phone.'
                                            '.$order->customer->phone:""}}</strong> </span> <br>
                                    <!--<span>WhatsApp : <strong>{{$order->customer?$order->customer->country_code_whatsapp.' '.$order->customer->whatsapp_no:""}}</strong> </span>-->
                                </p>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">{{ $order->total_amount }}</p>
                            </td>
                            <td>
                                <p class="small text-muted mb-1 text-uppercase">
                                    {{$order->createdBy?strtoupper($order->createdBy->name .'
                                    '.$order->createdBy->surname):""}}</p>
                            </td>

                            <td>

                                <span class="badge bg-{{ $order->status_class }}">{{ $order->status_label }}</span>
                            </td>
                            <td class="text-center">
                                
                               
                                @php
                                $userDesignationId = auth()->guard('admin')->user()->designation;
                                 $userId = auth()->guard('admin')->id();
                                @endphp

                                @if(empty($order->packingslip))
                                @if($order->status!="Cancelled")

                                {{-- New Code By Souvik --}}
                                @if($userDesignationId == 1 && ($order->status == 'Partial Approved By TL' || $order->status == 'Fully Approved By TL' || $order->status == 'Partial Approved By Admin' || $order->status == 'On Hold'))
                                    @if ($order->status != 'On Hold')
                                    <a href="{{ route('admin.order.add_order_slip', $order->id) }}"
                                        class="btn btn-outline-success select-md btn_outline">
                                        Approve Order
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.order.edit', $order->id) }}"
                                        class="btn btn-outline-success select-md btn_outline">
                                        Edit
                                    </a>
                                    <button wire:click="confirmCancelOrder({{ $order->id }})"
                                        class="btn btn-outline-danger select-md btn_outline">
                                        Cancel Order
                                    </button>
                                @endif

                                {{-- (Optional) TL Approve button --}}
                                @if(($userDesignationId == 4 && in_array($order->status, ['Approval Pending from TL', 'Partial Approved By TL','On Hold']) && $order->created_by != $userId) && ($order->hasProcessAndHoldItems() || $order->canTLApprove()))
                                <a href="{{ route('admin.order.add_order_slip', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Approve Order
                                </a>
                                @endif
                                 @if($userDesignationId == 4 && in_array($order->status, ['Approval Pending from TL', 'Partial Approved By TL','On Hold']))
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                @endif
                                 @if($userDesignationId == 4 && in_array($order->status, ['Approval Pending from TL', 'Partial Approved By TL','On Hold']) && $order->created_by != $userId)
                                <button wire:click="confirmCancelOrder({{ $order->id }})"
                                    class="btn btn-outline-danger select-md btn_outline">
                                    Cancel Order
                                </button>
                                @endif

                                {{-- Designation 2(Sales Person): Show only Edit and Cancel if status is Approval
                                Pending --}}
                                @if($userDesignationId == 2 && ($order->status == 'Approval Pending from TL' || $order->status == 'On Hold'))
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                <button wire:click="confirmCancelOrder({{ $order->id }})"
                                    class="btn btn-outline-danger select-md btn_outline">
                                    Cancel Order
                                </button>
                                @endif
                                {{-- New Code end By Souvik --}}
                                @endif
                                @else
                                {{-- Admin override: Show Approve/Edit/Cancel even if slip exists and status is
                                Approved By TL --}}
                                @if($userDesignationId == 1 && ($order->status == 'Partial Approved By TL' || $order->status == 'Fully Approved By TL' || $order->status == 'Partial Approved By Admin') && $order->hasProcessAndAdminApprovedItems())
                                <a href="{{ route('admin.order.add_order_slip', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Approve Order
                                </a>
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                <button wire:click="confirmCancelOrder({{ $order->id }})"
                                    class="btn btn-outline-danger select-md btn_outline">
                                    Cancel Order
                                </button>

                                @else
                              {{--  @if ($userDesignationId == 2 && $order->hasHoldItemsWithApprovedTLStatus())  --}}
                              @if ($userDesignationId == 2 && $order->canSalesEdit())
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                @endif
                                @if($userDesignationId == 1 && $order->canAdminApprove())
                                <a href="{{ route('admin.order.add_order_slip', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Approve Order
                                </a>
                                @endif
                              {{--  @if($userDesignationId != 2 && (($userDesignationId == 4 && $order->canTLApprove()) || $order->status == 'Partial Approved By Admin') && $order->hasProcessAndTLApprovedItems() || $order->hasProcessAndTLPendingItems()) --}}
                              @if(
                                    $userDesignationId != 2 &&
                                    (
                                        (
                                            ($userDesignationId == 4 && $order->canTLApprove())
                                            || $order->status == 'Partial Approved By Admin'
                                        )
                                        &&
                                        (
                                            $order->hasProcessAndTLApprovedItems()
                                            || $order->hasProcessAndTLPendingItems()
                                        )
                                    )
                                )
                                <a href="{{ route('admin.order.add_order_slip', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Approve Order
                                </a>

                                @endif

                                @if($userDesignationId == 4 && ($order->hasHoldItemsWithApprovedTLStatus() || $order->hasProcessAndTLPendingItems()))
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                @endif
                                @if($userDesignationId == 1 && $order->hasHoldItemsWithApprovedByAdmin())
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                @endif
                                @if($userDesignationId == 12 && $order->hasHoldItemsWithApprovedByAdmin())
                                <a href="{{ route('admin.order.edit', $order->id) }}"
                                    class="btn btn-outline-success select-md btn_outline">
                                    Edit
                                </a>
                                @endif


                                <a href="{{route('admin.order.download_invoice',$order->id)}}" target="_blank"
                                    class="btn btn-outline-primary select-md btn_outline">Invoice</a>
                                <a href="{{route('admin.order.download_bill',$order->id)}}" target="_blank"
                                    class="btn btn-outline-primary select-md btn_outline">Bill</a>
                                @endif

                                @endif
                                @if ($order->invoice_type=="invoice" )
                                <a href="{{route('admin.order.view',$order->id)}}"
                                    class="btn btn-outline-success select-md btn_action btn_outline">Details</a>
                                @endif
                                @if ($userDesignationId != 2 && !$order->skip_order_reason)
                                <a href="{{route('admin.order.log',$order->id)}}">
                                    <button class="btn btn-outline-info select-md btn_action btn_outline">Logs
                                    </button>
                                </a>
                                @endif
                                <a href="{{ route('orders.generatePdf', $order->id) }}"
                                class="btn btn-outline-success select-md btn_outline" target="_blank">
                                Receipt 
                                </a>
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
<div class="modal fade {{ $showImportModal ? 'show d-block' : '' }}" 
     style="{{ $showImportModal ? 'background:rgba(0,0,0,0.6);' : '' }}"
     wire:click.self="closeImportModal">

    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Import Orders</h5>
                <button type="button" class="btn-close" wire:click="closeImportModal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                 @if($importError)
                    <div class="alert alert-danger">{{ $importError }}</div>
                @endif
                
                <div class="mb-3">
                    <label>Upload Excel</label>
                    <input type="file" wire:model="import_file" class="form-control">
                    @error('import_file')
                       <p class="small text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="text-left">
                    <a href="{{ asset('assets/csv/order_sample.csv') }}" download 
                       class="btn btn-outline-primary">
                        Download Sample CSV
                    </a>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary select-md" wire:click="closeImportModal">
                    Cancel
                </button>
                <button class="btn btn-sm btn-success select-md" wire:click="importData">
                    Upload
                </button>
            </div>

        </div>
    </div>
</div>
</div>


@push('js')
<!--Chosen cdn start-->
<link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<!--Chosen cdn end--> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

<script>
    document.addEventListener('livewire:init', function () {
    
        function initChosen() {
            $('#created_by').chosen().change(function (e) {
                let selected = $(this).val();
                @this.set('created_by', selected);
            });
        }
    
        initChosen();
    
        // Re-init after Livewire updates DOM
        Livewire.hook('message.processed', () => {
            $('#created_by').chosen('destroy');
            initChosen();
        });
    
    });
</script>
@endpush