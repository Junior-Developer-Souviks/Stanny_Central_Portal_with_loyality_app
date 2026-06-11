<div>
    <div class="content-wrapper">
        <section class="admin__title">
            <h5> Customer Details</h5>
        </section>
        <section>
            <ul class="breadcrumb_menu">
                <li><a href="{{ route('customers.index') }}">Customers</a></li>
                <li>Customer Details</li>
                <li class="back-button">
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0" href="{{ route('customers.index') }}" role="button">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1">Back</span>
                </a>
                </li>
            </ul>
        </section>
        <!-- Content -->

        <div class="card shadow-sm mb-2">
        <div class="card-body">
            <div class="row">
                <div class="col">
                   <a href="{{ route('admin.customers.edit', ['id' => $customer->id]) }}" class="btn btn-outline-info custom-btn-sm">Edit</a>
                   <a wire:click="redirectToOrderPage" class="btn btn-outline-info custom-btn-sm">Place Order</a>
                   <a wire:click="redirectToOrderIndex" class="btn btn-outline-info custom-btn-sm">Order History</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group mb-3">
                        <div class="customer-details-wrap mb-4">
                            <h6>Customer Details</h6>
                            <div class="customer-details">
                                <div class="avatar me-3">
                                    {{--@dump($customer)--}}
                                    @if ($customer && $customer->profile_image)
                                        <img src="{{asset($customer->profile_image)}}" alt="Avatar"class="rounded-circle">
                                    @else
                                       <img src="{{asset("assets/img/profile_image.png")}}" alt="profile-image">
                                    @endif
                                </div>

                                <div class="pofile-details">
                                    <h6 class="text-nowrap mb-0 meduim-heading">{{$customer->name}}</h6>
                                    {{-- <h6 class="text-nowrap mb-0 meduim-heading">{{$customer->ordersAsCustomer->count()}} Orders</h6> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Contact info</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Email :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{$customer->email}}</p>
                            </div>
                        </div>
                        @if ($customer->company_name)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Company Name :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->company_name}}</p>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Phone :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{ $customer->country_code_phone.' '. $customer->phone }}</p>
                            </div>
                        </div>
                        @if($customer->whatsapp_no)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Whatsapp No :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->country_code_whatsapp.' '.$customer->whatsapp_no}}</p>
                            </div>
                        </div>
                        @endif
                        {{-- alternative number 1 --}}
                        @if($customer->alternative_phone_number_1)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Alternative No 1:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->country_code_alt_1.' '.$customer->alternative_phone_number_1}}</p>
                            </div>
                        </div>
                        @endif
                        {{-- alternative number 2 --}}
                        @if($customer->alternative_phone_number_2)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Alternative No 2:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->country_code_alt_2.' '.$customer->alternative_phone_number_2}}</p>
                            </div>
                        </div>
                        @endif
                        @if ($customer->dob)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>D.O.B. :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->dob}}</p>
                            </div>
                        </div>
                        @endif
                        @if($customer->employee_rank)
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Employee Rank :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$customer->employee_rank}}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    {{-- <div class="form-group mb-3">
                        <h6>Shipping Address</h6>
                        <div class="row">
                        @if($customer->shippingAddressLatest)
                                <p>{{ implode(', ', array_filter([
                                    $customer->shippingAddressLatest->address ?? '',
                                    $customer->shippingAddressLatest->landmark ?? '',
                                    $customer->shippingAddressLatest->city ?? '',
                                    $customer->shippingAddressLatest->state ?? '',
                                    $customer->shippingAddressLatest->country ?? ''
                                ])) }}{{ $customer->shippingAddressLatest->zip_code ? ' - ' . $customer->shippingAddressLatest->zip_code : '' }}
                                </p>
                            @else
                                <p>No shipping address available.</p>
                            @endif
                        </div>
                    </div> --}}
                    <div class="form-group mb-3">
                        <h6> Address</h6>
                        <div class="row">
                        @if($customer->billingAddressLatest)
                                <p>{{ implode(', ', array_filter([
                                        $customer->billingAddressLatest->address ?? '',
                                        $customer->billingAddressLatest->landmark ?? '',
                                        $customer->billingAddressLatest->city ?? '',
                                        $customer->billingAddressLatest->state ?? '',
                                        $customer->billingAddressLatest->country ?? '',
                                        $customer->billingAddressLatest->zip_code ?? ''
                                    ])) }}</p>
                            @else
                                <p>No billing address available.</p>
                            @endif
                        </div>
                      
    
                    </div>

                    {{-- <div class="form-group mb-3">
                    <h6 class="card-title">Account Information</h6>
                         @if($customer->gst_certificate_image !=""||$customer->gst_number !=""||$customer->credit_limit !=""||$customer->credit_days !="")
                            <div class="avatar me-3">
                                @if ($customer->gst_certificate_image)
                                <img src="{{asset($customer->gst_certificate_image)}}" alt="Avatar"
                                    class="rounded-circle">
                                @endif
                            </div>
                                @if($customer->gst_number)
                            <p class="mb-1"><i class="fas fa-id-card" style="font-size: 14px; color: #6c757d;"></i>
                                {{$customer->gst_number}}
                            </p>
                                @endif 
                                @if($customer->credit_limit)
                            <p class="mb-1"><i class="fas fa-credit-card" style="font-size: 14px; color: #6c757d;"></i>
                                {{$customer->credit_limit}}
                            </p>
                            @endif 
                            @if($customer->credit_days)
                            <p class="mb-1"><i class="fas fa-calendar-day" style="font-size: 14px; color: #6c757d;"></i>
                                {{$customer->credit_days}}
                            </p>
                            @endif 

                            @else
                        <div class="card-body">
                            <p class="mb-1 text-muted">
                                <i class="fas fa-info-circle" style="font-size: 14px; color: #6c757d;"></i>
                                No information found.
                            </p>
                        </div>
                        @endif
                      
    
                    </div> --}}
                    
                    
                </div>
            </div>
        </div>
    </div>
        <div class="card shadow-sm mb-2">
            <!-- Order Details Table -->
            {{-- <div class="row"> --}}
                {{-- <div class="col-12 col-lg-12"> --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">Latest 10 Orders</h5>
                        </div>
                        <div class="card-body">
                            <!-- Add table-responsive for responsiveness -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover ledger">
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Customer Name</th>
                                            <th>Billing Amount</th>
                                            <th>Remaining Amount</th>
                                            <th>Updated Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestOrders as $latestOrder)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.order.view', $latestOrder->id) }}" class="text-primary">
                                                    {{ $latestOrder->order_number }}
                                                </a>
                                            </td>
                                            <td>{{ $latestOrder->customer->name }}</td>
                                            <td>{{ number_format($latestOrder->total_amount, 2) }}</td>
                                            <td class="{{ $latestOrder->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($latestOrder->remaining_amount, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-danger custom_danger_badge">
                                                    {{ $latestOrder->updated_at }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {{-- </div> --}}
            {{-- </div> --}}
        </div>
    </div>
</div>