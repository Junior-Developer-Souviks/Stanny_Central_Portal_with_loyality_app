<div class="container">
    <section class="admin__title">
        <h5>Payment Collection</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Accounting</li>
            <li><a href="{{route('admin.accounting.payment_collection')}}">Payment Collection</a></li>
            <li class="back-button"></li>
          </ul>
    </section>
    <div class="search__filter">
        <div class="row align-items-center justify-content-end">
            <div class="col-auto">
                <div class="row g-3 align-items-center">
                    <div class="col-auto mt-0">
                        <input type="text" wire:model="selected_customer" class="form-control select-md bg-white" id="customer"
                            placeholder="Search customer by name or order number" wire:keyup="FindCustomer($event.target.value)" value=""
                            style="width: 350px;">
                        @if(!empty($searchResults))
                            <div id="fetch_customer_details" class="dropdown-menu show" style="max-height: 200px; width: 350px; overflow-y: auto;">
                                @foreach ($searchResults as $customer_item)
                                    <button class="dropdown-item" type="button" wire:click="selectCustomer({{ $customer_item->id }})">
                                        <img src="{{ $customer_item->profile_image ? asset($customer_item->profile_image) : asset('assets/img/user.png') }}" alt=""> {{ $customer_item->name }}  ({{ $customer_item->phone }})
                                    </button>
                                @endforeach
                            </div>
                        @endif

                    </div>
                    <div class="col-auto mt-0">
                        <select wire:model="staff_id" class="form-control select-md bg-white" wire:change="CollectedBy($event.target.value)">
                            <option value="" hidden="" selected="">Collected By</option>
                            @foreach($staffs as $staff)
                                <option value="{{$staff->id}}">{{ucwords($staff->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto mt-3">
                        <button type="button" wire:click="resetForm" class="btn btn-outline-danger select-md">Clear</button>
                    </div>
                    <div class="col-auto mt-3">
                       <a href="{{route('admin.accounting.add_payment_receipt')}}" class="btn btn-sm btn-success select-md"> <i class="material-icons">add</i>ADD PAYMENT RECEIPT</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-end">
            <div class="col-auto">
                <p class="text-sm font-weight-bold">{{$total}} Items</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover ledger">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Voucher No</th>
                                    <th>Payment Date</th>
                                    <th>Collected By</th>
                                    <th>Customer</th>
                                    <th>Collection Amount</th>
                                    <th>Collected From</th>
                                    <th>Approval</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @forelse($paymentData as $index => $payment)

                                    <tr class="store_details_row cursor-pointer {{$active_details==$payment->id?"tr_active":""}}" wire:click="customerDetails({{$payment->id}})">
                                        <td>{{$index+1}}</td>
                                        <td>{{$payment->voucher_no}}</td>
                                            <td>
                                            <p class="small text-muted mb-1">
                                                {{date('d/m/Y', strtotime($payment->cheque_date))}}
                                            </p>
                                        </td>
                                        <td>
                                            @if (!empty($payment->user))
                                                <p class="small text-muted mb-1">{{ucfirst($payment->user->name)}} @if($payment->user->surname){{ucfirst ($payment->user->surname)}}@endif</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="small text-muted mb-1">
                                                @if (!empty($payment->customer->name))
                                                <span><strong>{{ucfirst($payment->customer->name)}}</strong> </span>
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            <p class="small text-muted mb-1">Rs. {{number_format((float)$payment->collection_amount, 2, '.', '')}} ({{ucwords($payment->payment_type)}})</p>
                                        </td>

                                        <td>
                                            <span class="badge bg-success">{{ucwords($payment->created_from)}}</span>
                                        </td>
                                        <td>
                                            @if (!empty($payment->is_ledger_added))
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-danger">Not Approved</span>

                                            @endif
                                        </td>
                                       
                                        <td>
                                            @if (empty($payment->is_ledger_added) && $canApprove)
                                                <a href="{{ route('admin.accounting.payment_receipt_detail',$payment->id) }}" class="btn btn-md btn-warning select-md btn_outline">Approve</a>
                                            @endif

                                            @if (!empty($payment->is_ledger_added))
                                                <a href="#" wire:click="$dispatch('confirm-revoke',{{$payment->id}})" class="btn btn-outline-warning select-md btn_outline">Revoke</a>
                                            @endif
                                            @if (!empty($payment->payment_type) and $payment->payment_type=='cheque')
                                                <a href="{{ route('admin.accounting.edit_cheque_info', ['payment_voucher_no' => $payment->voucher_no]) }}" class="btn btn-outline-warning select-md btn_outline">Edit Check Info</a>
                                            @endif
                                            <button wire:click="downloadInvoice({{ $payment->id }})" class="btn btn-outline-primary select-md btn_outline">Download Receipt</button>

                                        </td>
                                    </tr>
                                    @if($active_details==$payment->id)
                                    <tr>
                                        <td colspan="5" class="store_details_column">
                                            <div class="store_details">
                                                <table class="table">
                                                    <tr>
                                                        <td>
                                                            <span>Customer Name: <strong>{{$payment->customer->name}} </strong> </span>
                                                        </td>
                                                        @if (!empty($payment->customer->name))
                                                        <td>
                                                            <span>Company Name: <strong>{{$payment->customer->company_name}} </strong> </span>
                                                        </td>
                                                        @endif
                                                        @if (!empty($payment->customer->phone))
                                                        <td>
                                                            <span>Phone: <strong>{{$payment->customer->phone}} </strong> </span>
                                                        </td>
                                                        @endif
                                                        <td>
                                                            <span>Receipt Copy: </span>
                                                    
                                                            @if (!empty($payment->receipt_copy_upload))
                                                                <a href="{{ asset($payment->receipt_copy_upload) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-sm btn-outline-success select-md mt-2">
                                                                    View Receipt
                                                                </a>
                                                            @else
                                                                <span class="text-danger">No File</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        @if (!empty($payment->bank_name))
                                                        <td><span>Bank: <strong>{{ ($payment->bank_name)}}</strong></span></td>
                                                        @endif
                                                        @if (!empty($payment->payment_type))
                                                        <td><span>Bank: <strong>{{ ucwords($payment->payment_type)}}</strong></span></td>
                                                        @endif
                                                        @if (!empty($payment->chq_utr_no))
                                                        <td><span>Cheque / UTR No: <strong>{{ ucwords($payment->cheque_number)}}</strong></span></td>
                                                        @endif
                                                        @if (!empty($payment->cheque_date))
                                                        <td><span>Payment Date: <strong>{{ date('d/m/Y', strtotime($payment->cheque_date))}}</strong></span></td>
                                                        @endif
                                                        @if (!empty($payment->vouchar_no))
                                                        <td><span>Voucher No: <strong>{{ ($payment->vouchar_no)}}</strong></span></td>
                                                        @endif
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="9" class="border px-4 py-2 text-center">No data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $paymentData->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
{{-- script --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('confirm-revoke', event => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will revoke the payment and reset linked invoices and payments!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, revoke it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('revoke-payment-confirmed', { id: event.detail });
            }
        });
    });
</script>
</div>

