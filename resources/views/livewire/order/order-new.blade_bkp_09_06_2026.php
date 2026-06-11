<div class="container">
    <style>
        .form-control {
            cursor: pointer;
        }
        .message-icon:hover {
            opacity: 1 !important;
            color: #007bff; /* Optional: adds a blue color on hover */
        }
       
        
    </style>
    <section class="admin__title">
        <h5>Place Order</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Sales Management</li>
            <li><a href="{{route('admin.order.new')}}">Place Order</a></li>
            <li class="back-button">
                @if($activeTab==1)
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0"
                    href="{{route('admin.order.index')}}" role="button">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1">Back</span>
                </a>
                @endif
            </li>
        </ul>
    </section>
    <div class="card my-4">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
            </div>
        </div>

        <div class="card-body" id="sales_order_data">
             
            
            <form wire:submit.prevent="save">
                <div class="{{$activeTab==1?" d-block":"d-none"}}" id="tab1">
                    {{-- checkbox section --}}
                    <div class="mb-0">
                        <label><strong>Select Customer Type:</strong></label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div classs="">
                                <div class="form-check form-check-inline mx-0 px-0">
                                    <input class="form-check-input" type="radio"
                                        wire:change="onCustomerTypeChange($event.target.value)"
                                        wire:model="customerType" id="newCustomer" value="new" checked>
                                    <label class="form-check-label" for="newCustomer">New Customer</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                        wire:change="onCustomerTypeChange($event.target.value)"
                                        wire:model="customerType" id="existingCustomer" value="existing">
                                    <label class="form-check-label" for="existingCustomer">Existing Customer</label>
                                </div>
                            </div>

                            <div>
                                <button class="btn btn-outline-success btn-sm" wire:click="skipOrderBill">Skip Order
                                    Bill</button>
                            </div>
                        </div>
                    </div>
                    {{-- Skip Modal start--}}
                    <div wire:ignore.self class="modal fade" id="skipModal" tabindex="-1"
                        aria-labelledby="skipModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="skipModalLabel">
                                        Skip Order Number
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-5">
                                    <div class="row g-4">
                                        <!-- Bill Number -->
                                        <div class="col-md-6">
                                            <label class="form-label"><strong>Bill Number</strong></label>
                                            <input type="text"
                                                class="form-control form-control-sm text-center border border-1"
                                                readonly wire:model="order_number" value="{{ $order_number }}">
                                              @error('order_number')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                        </div>
                                         <!--  Status Selection Radio Buttons -->
                                            <div class="col-md-6">
                                                <label class="form-label"><strong>Choose Action Type <span class="text-danger">*</span></strong></label>
                                                <div class="d-flex rounded-lg h-100 align-items-center bg-white">
                                                    <!-- Option: Cancelled -->
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input text-red-500" type="radio" name="selectedStatus"
                                                            id="statusCancelled" value="Cancelled" wire:model.live="selected_status" required>
                                                        <label class="form-check-label font-medium text-danger" for="statusCancelled">
                                                            Cancelled
                                                        </label>
                                                    </div>
                                                    <!-- Option: Hold -->
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input text-yellow-500" type="radio" name="selectedStatus"
                                                            id="statusHold" value="On Hold" wire:model.live="selected_status" required>
                                                        <label class="form-check-label font-medium text-success" for="statusHold">
                                                            Hold
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="col-12">
                                            <label class="form-label"><strong>Reason <span
                                                        class="text-danger">*</span></strong></label>
                                            <textarea class="form-control form-control-sm border border-1"
                                                wire:model="skip_order_reason" rows="3"></textarea>
                                            @error('skip_order_reason')
                                            <p class="text-danger small">{{$message}}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" wire:click="skipOrder" class="btn btn-success">Skip</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Skip Modal end--}}
                    <div class="row align-items-center mb-3">
                        @php
                            $auth = Auth::guard('admin')->user();
                            $priority_level = in_array($auth->designation,[1,4]);
                        @endphp
                        {{-- <div class="col-md-4 {{ $auth->is_super_admin==1 ? "" : " d-none" }}"> --}}
                            <div class="col-md-4">
                                <!-- Search Label -->
                                <label for="searchCustomer" class="form-label mb-0">Business Type</label>
                                <select wire:model="selectedBusinessType"
                                    class="form-select me-2 form-control form-control-sm border border-1 customer_input"
                                    aria-label="Default select example">
                                    <option selected hidden>Select Domain</option>
                                    @foreach ($Business_type as $domain)
                                    <option value="{{$domain->id}}">{{$domain->title}}</option>
                                    @endforeach
                                </select>
                                @if(isset($errorMessage['selectedBusinessType']))
                                <div class="text-danger error-message">{{ $errorMessage['selectedBusinessType'] }}</div>
                                @endif
                            </div>
                            {{-- Display Order by and order number --}}
                            <!-- Ordered By Section -->
                            <div class="col-md-4">
                                <label class="form-label"><strong>Ordered By</strong></label>
                                <select
                                    class="form-control border border-2 p-2 form-control-sm @error('salesman') border-danger  @enderror"
                                    wire:change="changeSalesman($event.target.value)" wire:model="salesman" disabled>
                                    <option value="" selected hidden>Choose one..</option>
                                    <!-- Set authenticated user as default -->
                                    @if(auth()->guard('admin')->check())
                                    <option value="{{auth()->guard('admin')->user()->id}}" selected>
                                        {{strtoupper(auth()->guard('admin')->user()->name)}}
                                    </option>
                                    @endif

                                    <!-- Other Salesmen -->
                                    @foreach ($salesmen as $salesmans)
                                    @if ($salesmans->id != auth()->guard('admin')->user()->id)
                                    <option value="{{ $salesmans->id }}">{{ strtoupper($salesmans->name.'
                                        '.$salesmans->surname) }}</option>
                                    @endif
                                    @endforeach
                                </select>
                                @if(isset($errorMessage['salesman']))
                                <div class="text-danger error-message">{{ $errorMessage['salesman'] }}</div>
                                @endif
                            </div>

                            <!-- Bill Number -->
                            <div class="col-md-4">
                                <label class="form-label"><strong>Bill Number</strong></label>
                                <input type="text" class="form-control form-control-sm text-center border border-1"
                                    disabled wire:model="order_number" value="{{ $order_number }}">
                                @if(isset($errorMessage['order_number']))
                                <div class="text-danger error-message">{{ $errorMessage['order_number'] }}</div>
                                @endif
                               
                            </div>

                            @if ($customerType == 'existing')
                            <!-- Search Label and Select2 -->
                            <div class="col-md-6 mt-2">
                                <div class="d-flex justify-content-between">
                                    <!-- Search Label -->
                                    <label for="searchCustomer" class="form-label mb-0">Customer</label>
                                </div>

                                <div class="position-relative">
                                    <input type="text" wire:keyup="FindCustomer($event.target.value)"
                                        wire:model.debounce.500ms="searchTerm"
                                        class="form-control form-control-sm border border-1 customer_input"
                                        placeholder="Search by customer details or order ID">

                                    @if(!empty($searchResults))
                                    <div id="fetch_customer_details" class="dropdown-menu show w-100"
                                        style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($searchResults as $customer)
                                        <button class="dropdown-item" type="button"
                                            wire:click="selectCustomer({{ $customer->id }})">
                                            <img src="{{ $customer->profile_image ? asset($customer->profile_image) : asset('assets/img/user.png') }}"
                                                alt=""> {{ucfirst($customer->prefix . " ". $customer->name) }} ({{
                                            $customer->country_code_phone .' '.$customer->phone
                                            }})
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                        </div>

                        <!-- Order Customer Fields... -->
                        @if(session()->has('orders-found') && $orders?->count() > 0)
                        <div class="alert alert-success mt-3">
                            {{ session('orders-found') }}
                        </div>
                        @endif

                        @if (session()->has('no-orders-found'))
                        <div class="alert alert-danger mt-3">
                            {{ session('no-orders-found') }}
                        </div>
                        @endif
                        @if(!empty($orders) && $orders->count())
                        <h6 class="mb-3">Previous Order Details</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr class="text-center">
                                    <th>Order Number</th>
                                    <th>Customer Name</th>
                                    <th>Billing Amount</th>
                                    <th>Billing Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                <tr class="text-center">
                                    <td>{{ $order->order_number }}</td>
                                    <td>
                                        {{ $order->customer ? $order->customer->prefix ." ".$order->customer->name : ""
                                        }}
                                    </td>
                                    <td>{{ $order->total_amount }}</td>
                                    <td>{{ $order->last_payment_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.order.invoice', $order->id) }}"
                                            class="btn btn-outline-primary btn-sm" target="_blank">
                                            Invoice
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                        <!-- Customer Details -->
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <h6 class="badge bg-danger custom_danger_badge">Basic Information</h6>
                        </div>
                        <!-- Customer Details -->
                        <div class="row">
                            <div class="mb-2 col-md-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select wire:model="prefix"
                                        class="form-control form-control-sm border border-1 prefix_select flex-30">
                                        <option value="" selected hidden>Prefix</option>
                                        @foreach (App\Helpers\Helper::getNamePrefixes() as $prefixOption)
                                        <option value="{{$prefixOption}}">{{ $prefixOption }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="name" id="name"
                                        class="form-control form-control-sm border border-1 p-2 {{ $errorClass['name'] ?? '' }}"
                                        placeholder="Enter customer name" wire:keydown.enter.prevent>
                                </div>
                                @if(isset($errorMessage['prefix']))
                                <div class="text-danger error-message">{{ $errorMessage['prefix'] }}</div>
                                @endif
                                @if(isset($errorMessage['name']))
                                <div class="text-danger error-message">{{ $errorMessage['name'] }}</div>
                                @endif
                            </div>


                            <div class="mb-2 col-md-2">
                                <label for="employee_rank" class="form-label"> Rank</label>
                                <input type="text" wire:model="employee_rank" id="employee_rank"
                                    class="form-control form-control-sm border border-1 p-2" placeholder="Enter rank"
                                    wire:keydown.enter.prevent>
                            </div>

                            <div class="mb-2 col-md-4">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" wire:model="company_name" id="company_name"
                                    class="form-control form-control-sm border border-1 p-2"
                                    placeholder="Enter company name" wire:keydown.enter.prevent>
                            </div>

                            <div class="mb-2 col-md-3">
                                <label for="email" class="form-label">Email </label>
                                <input type="email" wire:model="email" id="email"
                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['email'] ?? '' }}"
                                    placeholder="Enter email" wire:keydown.enter.prevent>
                                @if(isset($errorMessage['email']))
                                <div class="text-danger error-message">{{ $errorMessage['email'] }}</div>
                                @endif
                            </div>



                            <div class="mb-2 col-md-3">
                                <label for="dob" class="form-label">Date Of Birth</label>
                                <input type="date" autocomplete="bday" wire:model="dob" id="dob"
                                    max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['dob'] ?? '' }}"
                                    wire:keydown.enter.prevent>
                                {{-- @if(isset($errorMessage['dob']))
                                <div class="text-danger">{{ $errorMessage['dob'] }}</div>
                                @endif --}}
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3 col-md-3">
                                <label for="mobile" class="form-label">Phone Number</label>
                                <div class="input-group input-group-sm" id="parent_mobile" wire:ignore>
                                    <input id="mobile" type="tel" class="form-control tel-code-input"
                                        style="width:286px;" wire:keydown.enter.prevent>
                                    <!-- hidden Livewire bindings -->
                                    <input type="hidden" wire:model="phone_code" id="phone_code">
                                    <input type="hidden" wire:model="phone" id="phone">

                                </div>

                                @if(isset($errorMessage['phone']))
                                <div class="text-danger error-message">{{ $errorMessage['phone'] }}</div>
                                @enderror
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp1" wire:model="isWhatsappPhone"
                                        wire:keydown.enter.prevent>
                                    <label for="is_whatsapp1" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                            <!-- Alternative Phone Number 1 -->
                            <div class="mb-3 col-md-3">
                                <label for="alt_phone_1" class="form-label">Alternative Phone 1</label>
                                <div class="input-group input-group-sm" id="parent_alt_phone_code_1" wire:ignore>
                                    <input id="alt_phone_1" type="tel" class="form-control tel-code-input"
                                        style="width:269px;" wire:keydown.enter.prevent>
                                    <input type="hidden" wire:model="alt_phone_code_1" id="alt_phone_code_1">
                                    <input type="hidden" wire:model="alternative_phone_number_1"
                                        id="alt_phone_hidden_1">
                                </div>
                                @if(isset($errorMessage['alternative_phone_number_1']))
                                <div class="text-danger error-message">{{ $errorMessage['alternative_phone_number_1'] }}
                                </div>
                                @endif
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp2" wire:model="isWhatsappAlt1"
                                        wire:keydown.enter.prevent>
                                    <label for="is_whatsapp2" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                            <!-- Alternative Phone Number 2 -->
                            <div class="mb-3 col-md-3">
                                <label for="alt_phone_2" class="form-label">Alternative Phone 2</label>
                                <div class="input-group input-group-sm" id="parent_alt_phone_code_2" wire:ignore>
                                    <input id="alt_phone_2" type="tel" class="form-control tel-code-input"
                                        style="width:269px;" wire:keydown.enter.prevent>
                                    <input type="hidden" wire:model="alt_phone_code_2" id="alt_phone_code_2">
                                    <input type="hidden" wire:model="alternative_phone_number_2"
                                        id="alt_phone_hidden_2">
                                </div>
                                @if(isset($errorMessage['alternative_phone_number_2']))
                                <div class="text-danger error-message">{{ $errorMessage['alternative_phone_number_2'] }}
                                </div>
                                @endif
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp3" wire:model="isWhatsappAlt2"
                                        wire:keydown.enter.prevent>
                                    <label for="is_whatsapp3" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                            {{-- <div class="mb-2 col-md-3">
                                <label for="customer_image" class="form-label">Client Image <span
                                        class="small text-danger">*</span></label>
                                <input type="file" wire:model="customer_image" id="customer_image"
                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['customer_image'] ?? '' }}">
                                @if(isset($errorMessage['customer_image']))
                                <div class="text-danger error-message">{{ $errorMessage['customer_image'] }}</div>
                                @endif
                            </div> --}}


                        </div>

                        <div class="">
                            <div class="">
                                <h6 class="badge bg-danger custom_danger_badge">Address</h6>
                            </div>
                            <div class="pt-0">
                                <div class="admin__content">
                                    <aside>
                                        <nav class="text-uppercase font-weight-bold"> Address</nav>
                                    </aside>
                                    <content>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="" class="col-form-label">Address <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-9">
                                                <input type="text" id="billing_addr"
                                                    class="form-control form-control-sm" wire:model="billing_address"
                                                    value="" wire:keydown.enter.prevent>
                                                @if(isset($errorMessage['billing_address']))
                                                <div class="text-danger error-message">{{
                                                    $errorMessage['billing_address']
                                                    }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="" class="col-form-label">Landmark</label>
                                            </div>
                                            <div class="col-9">
                                                <input type="text" id="billing_landmark"
                                                    class="form-control form-control-sm" wire:model="billing_landmark"
                                                    value="" wire:keydown.enter.prevent>
                                                @if(isset($errorMessage['billing_landmark']))
                                                <div class="text-danger error-message">{{
                                                    $errorMessage['billing_landmark']
                                                    }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="" class="col-form-label">City <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" id="billing_city"
                                                    class="form-control form-control-sm" wire:model="billing_city"
                                                    value="" wire:keydown.enter.prevent>
                                                @if(isset($errorMessage['billing_city']))
                                                <div class="text-danger error-message">{{ $errorMessage['billing_city']
                                                    }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="" class="col-form-label">Country <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" id="billing_country"
                                                    class="form-control form-control-sm" wire:model="billing_country"
                                                    value="" wire:keydown.enter.prevent>
                                                @if(isset($errorMessage['billing_country']))
                                                <div class="text-danger error-message">{{
                                                    $errorMessage['billing_country']
                                                    }}</div>
                                                @endif
                                            </div>
                                            <div class="col-3 text-end">
                                                <label for="" class="col-form-label">Pincode</label>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" id="billing_pin" class="form-control form-control-sm"
                                                    wire:model="billing_pin" value="" wire:keydown.enter.prevent>
                                                @if(isset($errorMessage['billing_pin']))
                                                <div class="text-danger error-message">{{ $errorMessage['billing_pin']
                                                    }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </content>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="{{$activeTab==2?" d-block":"d-none"}}" id="tab2">
                        <div class="row">
                            <div class="col-12 col-md-12 mb-2 mb-md-0">
                                <h6 class="badge bg-danger custom_danger_badge">Product Information</h6>
                                {{--Auto Save Code--}}
                             {{--   <button type="button" class="btn btn-info btn-sm" wire:click="manualSaveDraft">
                                 <i class="fas fa-save"></i> Save Draft Now
                                </button> --}}
                                
                                {{--Auto Save Code End--}}
                            </div>
                        </div>
                        @if ($errors->has('items'))
                        <div class="alert alert-danger">
                            {{ $errors->first('items') }}
                        </div>
                        @endif
                        {{-- Display Order by and order number --}}
                        <!-- Ordered By Section -->
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Ordered By</strong></label>
                                <input type="text"
                                    class="form-control border border-2 p-2 form-control-sm @error('salesman') border-danger  @enderror"
                                    value="{{ auth()->guard('admin')->check() && $salesman == auth()->guard('admin')->user()->id ? auth()->guard('admin')->user()->name : optional($salesmen->where('id', $salesman)->first())->name }}"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Bill Number</strong></label>
                                <!-- Remaining Amount -->
                                <input type="text" class="form-control form-control-sm text-center border border-1"
                                    disabled value="{{$order_number}}" readonly>
                            </div>
                        </div>
                        
                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label"><strong>Customer Name</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{$prefix .' '. $name }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"><strong>Rank</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $employee_rank }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Company Name</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $company_name }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $email }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Date of Birth</strong></label>
                                <input type="date" class="form-control form-control-sm" value="{{ $dob }}" readonly>
                            </div>
                            
                        
                            <div class="col-md-3">
                                <label class="form-label"><strong>Phone</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $phone_code.' '.$phone }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Alternative Phone 1</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $alt_phone_code_1.' '.$alternative_phone_number_1 }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Alternative Phone 2</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $alt_phone_code_2.' '.$alternative_phone_number_2 }}" readonly>
                            </div>
                        
                        </div>
                     
                        
                        <div class="row mb-4 bg-light p-3 border-radius-lg border border-1 mx-1">

                            @php
                                $hasGarment = collect($items)->contains('collection', 1);
                            @endphp
                        
                            @if($hasGarment)
                        
                                <!-- Customer Image Upload + Preview -->
                                <div class="col-md-5">
                        
                                    <label for="customer_image" class="form-label">
                                        <strong>Client Profile Image (For Garments)</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                        
                                    <input 
                                        type="file" 
                                        wire:model="customer_image" 
                                        id="customer_image" 
                                        multiple
                                        class="form-control form-control-sm border border-1 p-2 @error('customer_image') border-danger @enderror"
                                    >
                        
                                    <div wire:loading wire:target="customer_image" class="text-info small mt-1">
                                        Uploading image...
                                    </div>
                        
                                    @error('customer_image')
                                        <div class="text-danger error-message small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                        
                                    <!-- Preview UNDER input -->
                                    @if ($customer_image)
                                        <div class="mt-3">
                                            <p class="small mb-2 text-bold">Profile Preview:</p>
                        
                                           @foreach ((is_array($customer_image) ? $customer_image : [$customer_image]) as $image)
                                                @if (is_object($image) && method_exists($image, 'temporaryUrl'))
                                                    <!-- Live Uploaded File -->
                                                    <img src="{{ $image->temporaryUrl() }}"
                                                         style="width: 100px; height: 100px; object-fit: cover;"
                                                         class="img-thumbnail shadow-sm me-2 mb-2">
                                                @elseif (is_string($image))
                                                    <!-- Restored from Draft (URL) -->
                                                    <img src="{{ $image }}"
                                                         style="width: 100px; height: 100px; object-fit: cover;"
                                                         class="img-thumbnail shadow-sm me-2 mb-2">
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                        
                                </div>
                        
                            @endif
                        
                        
                            <!-- Physical Bill Upload -->
                            <div class="col-md-5">
                        
                                <label for="physical_bill_book" class="form-label">
                                    <strong>Physical Bill Book Upload</strong>
                                    <span class="text-danger">*</span>
                                </label>
                        
                                <input 
                                    type="file" 
                                    wire:model="physical_bill_book" multiple
                                    id="physical_bill_book" 
                                    class="form-control border border-2 p-2 form-control-sm @error('physical_bill_book') border-danger @enderror"
                                >
                        
                                @error('physical_bill_book')
                                    <div class="text-danger error-message small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                                
                                <!-- Preview -->
                                @if ($physical_bill_book)

                                    @php
                                        $file = is_array($physical_bill_book)
                                            ? ($physical_bill_book[0] ?? null)
                                            : $physical_bill_book;
                                    @endphp
                                
                                    @if ($file)
                                
                                        <div class="mt-3">
                                            <p class="small mb-2 text-bold">Bill Book Preview:</p>
                                
                                            @if (is_object($file) && method_exists($file, 'temporaryUrl'))
                                
                                                @if (str_starts_with($file->getMimeType(), 'image/'))
                                                    <img src="{{ $file->temporaryUrl() }}"
                                                         style="max-width:100px;max-height:100px;object-fit:cover;"
                                                         class="img-thumbnail shadow-sm">
                                                @else
                                                   <a href="{{ $file->temporaryUrl() }}" 
                                                       target="_blank" 
                                                       rel="noopener noreferrer"
                                                       class="btn btn-primary btn-sm">
                                                       View Bill Book
                                                    </a>
                                                @endif
                                
                                            @elseif (is_string($file))
                                        
                                                <img src="{{ $file }}"
                                                     style="max-width:200px;max-height:200px;object-fit:cover;"
                                                     class="img-thumbnail shadow-sm">
                                
                                            @endif
                                        </div>
                                
                                    @endif
                                
                                @endif
                        
                            </div>
                        
                        </div>
                        
                      
                        <!-- Loop through items -->
                        @foreach($items as $index => $item)
                        <div class="row align-items-top mt-3">
                            <div class="col-auto mt-4">
                                <span class="text-sm badge bg-primary sale_grn_sl">{{$index + 1}}</span>
                            </div>
                            <!-- Collection  -->
                            <div class="col-md-2 col-12 mb-3">
                                <label class="form-label"><strong>Collection </strong><span
                                        class="text-danger">*</span></label>
                                <select wire:model="items.{{ $index }}.collection"
                                    wire:change="GetCategory($event.target.value, {{ $index }})"
                                    class="form-control border border-2 p-2 form-control-sm @error('items.'.$index.'.collection') border-danger @enderror">
                                    <option value="" selected hidden>Select collection</option>
                                    @foreach($collections as $citems)
                                    <option value="{{ $citems->id }}">{{ strtoupper($citems->title) }}
                                        @if($citems->short_code)({{ $citems->short_code }})@endif</option>
                                    @endforeach
                                </select>
                                @error("items.".$index.".collection")
                                <div class='text-danger'>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="col-md-2 col-12 mb-3">
                                <label class="form-label"><strong>Category</strong> <span
                                        class="text-danger">*</span></label>
                                <select wire:model="items.{{ $index }}.category"
                                    class="form-select form-control-sm border border-1 @error('items.'.$index.'.category') border-danger @enderror"
                                    wire:change="CategoryWiseProduct($event.target.value, {{ $index }})">

                                    <option value="" selected hidden>Select Category</option>

                                    @if (isset($items[$index]['categories']) && count($items[$index]['categories']) > 0)
                                    @foreach ($items[$index]['categories'] as $category)
                                    <option value="{{ $category['id'] }}" {{ (isset($items[$index]['category']) &&
                                        $items[$index]['category']==$category['id']) ? 'selected' : '' }}>
                                        {{ strtoupper($category['title']) }}
                                    </option>
                                    @endforeach

                                    @endif
                                </select>
                                @error("items.".$index.".category")
                                <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Product -->
                            @if(isset($items[$index]['collection']) && $items[$index]['collection'] == 1)

                            <div class="mb-3 col-md-2">
                                @else
                                <div class="col-md-2 col-12 mb-3">
                                    @endif
                                    <label class="form-label"><strong>Product</strong></label>
                                    <input type="text" wire:keyup="FindProduct($event.target.value, {{ $index }})"
                                        wire:focus="$set('activeProductDropdown', {{ $index }})"
                                        wire:blur="$set('activeProductDropdown', null)"
                                        wire:model="items.{{ $index }}.searchproduct"
                                        class="form-control form-control-sm border border-1 customer_input @error('items.'.$index.'.searchproduct') border-danger @enderror"
                                        placeholder="Enter product name"
                                        wire:change="validateSingle('items.{{ $index }}.searchproduct')"
                                        wire:keydown.enter.prevent>
                                    @if (session()->has('errorProduct.' . $index))
                                    <p class="text-danger">{{ session('errorProduct.' . $index) }}</p>
                                    @endif
                                 @if(isset($items[$index]['products']) && count($items[$index]['products']) > 0)
                                    <div id="fetch_customer_details" class="dropdown-menu show w-25"
                                        style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($items[$index]['products'] as $product)
                                        <button class="dropdown-item" type="button"
                                            wire:click='selectProduct({{ $index }}, "{{ $product->name }}", {{ $product->id }})'>
                                          
                                            {{ $product->name }}({{ $product->product_code }})
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                    @error("items.$index.searchproduct")
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(isset($items[$index]['collection']) && $items[$index]['collection'] != 1)
                                <div class="col-md-2 col-12 mb-3">
                                    <label class="form-label"><strong>Quantity</strong>
                                        @if($items[$index]['collection'] == 2)
                                        <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="number" wire:model="items.{{ $index }}.quantity" class="form-control form-control-sm border border-1 customer_input
                                        @error('items.' . $index . '.quantity') border-danger @enderror"
                                        placeholder="Enter quantity" min="1" wire:keyup="updateTotalAmount"
                                        wire:change="validateSingle('items.{{ $index }}.quantity')"
                                        wire:keydown.enter.prevent>
                                    @error('items.' . $index . '.quantity')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                @else
                                {{-- Hidden field for collection 1 --}}
                                <input type="hidden" wire:model="items.{{ $index }}.quantity">
                                @endif

                                @if(isset($items[$index]['collection']) && $items[$index]['collection'] == 1)
                                {{-- Fabric --}}
                                <div class="col-md-2 col-12 mb-3 position-relative">
                                    <label class="form-label"><strong>Fabric</strong></label>
                                    <input type="text" wire:model="items.{{ $index }}.searchTerm"
                                        wire:keyup="searchFabrics({{ $index }})" class="form-control form-control-sm"
                                        placeholder="Search by fabric name" id="searchFabric_{{ $index }}"
                                        autocomplete="off" wire:keydown.enter.prevent>
                                        
                                    @error("items.". $index .".searchTerm")
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror

                                  {{--  @if(!empty($items[$index]['searchResults']))
                                    <div class="dropdown-menu show w-100"
                                        style="max-height: 187px; max-width: 100px; overflow-y: auto;">
                                        @foreach ($items[$index]['searchResults'] as $fabric)
                                        <button class="dropdown-item fabric_dropdown_item" type="button"
                                            wire:click="selectFabric({{ $fabric['id'] }}, {{ $index }})">
                                            {{ $fabric['title'] }}({{$fabric['available_stock']}} m)
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif --}}
                                    {{-- Fabric Search Results --}}
                                        @if(!empty($items[$index]['searchResults']))
                                        <div class="dropdown-menu show w-100" 
                                             style="max-height: 187px; overflow-y: auto; z-index: 1050;">
                                            @foreach ($items[$index]['searchResults'] as $fabric)
                                                @php
                                                    $fabricId = is_array($fabric) ? ($fabric['id'] ?? '') : ($fabric->id ?? '');
                                                    $title    = is_array($fabric) ? ($fabric['title'] ?? '') : ($fabric->title ?? '');
                                                    $stock    = is_array($fabric) ? ($fabric['available_stock'] ?? 0) : ($fabric->available_stock ?? 0);
                                                @endphp
                                                <button class="dropdown-item fabric_dropdown_item" type="button"
                                                    wire:click="selectFabric({{ $fabricId }}, {{ $index }})">
                                                    {{ $title }} ({{ $stock }} m)
                                                </button>
                                            @endforeach
                                        </div>
                                        @endif
                                </div>
                               
                                {{-- Price --}}
                                <div class="col-md-2 col-12 mb-3">
                                    <div class="d-flex align-items-end">
                                        <!-- Price Input -->
                                        <div>
                                            <label class="form-label"><strong>Price</strong></label>
                                            <input type="text"
                                                wire:blur="checkproductPrice({{ $index }})"
                                                wire:model.defer="items.{{ $index }}.price" class="form-control form-control-sm border border-1 customer_input
                                            @if(session()->has('errorPrice.' . $index)) border-danger @endif
                                            @error('items.' . $index . '.price') border-danger  @enderror"
                                                placeholder="Enter Price" wire:keydown.enter.prevent>
                                        </div>

                                        <div>
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm danger_btn mb-0"
                                                wire:click="removeItem({{ $index }})">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Error Messages -->
                                    @if(session()->has('errorPrice.' . $index))
                                    <div class="text-danger error-message">{{ session('errorPrice.' . $index) }}</div>
                                    @endif

                                    @error('items.' . $index . '.price')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror

                                </div>

                                {{-- --}}
                                @else
                                {{--Accessories item Price --}}
                                <div class="col-md-3 col-12 mb-3">
                                    <div class="d-flex align-items-end gap-2">
                                        <div>
                                            <!-- Price Input -->
                                            <label class="form-label"><strong>Price</strong></label>
                                            <input type="text"
                                                 wire:blur="checkproductPrice({{ $index }})"
                                                wire:model="items.{{ $index }}.price" class="form-control form-control-sm border border-1 customer_input
                                                        @if(session()->has('errorPrice.' . $index)) border-danger @endif
                                                        @error('items.' . $index . '.price') border-danger  @enderror"
                                                placeholder="Enter Price" wire:keydown.enter.prevent>
                                        </div>
                                        <div>
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm danger_btn mb-0"
                                                wire:click="removeItem({{ $index }})"><span
                                                    class="material-icons">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Error Messages -->
                                    @if(session()->has('errorPrice.' . $index))
                                    <div class="text-danger error-message">{{ session('errorPrice.' . $index) }}</div>
                                    @endif

                                    @error('items.' . $index . '.price')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(isset($items[$index]['collection']) && $items[$index]['collection'] == 2)
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label for="">Delivery Date</label>
                                        <input type="month" class="form-control form-control-sm border border-1"
                                           min="{{ \Carbon\Carbon::today()->format('Y-m') }}"
                                            wire:model="items.{{$index}}.expected_delivery_date"
                                            wire:change="validateSingle('items.{{ $index }}.expected_delivery_date')"
                                            wire:keydown.enter.prevent>
                                        @error("items.$index.expected_delivery_date")
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @if ($priority_level)
                                    <div class="col-md-2">
                                        <label class="form-label"><strong>Priority Level</strong></label>
                                        <select class="form-control form-control-sm border border-1"
                                            wire:model="items.{{ $index }}.priority"
                                            wire:change="validateSingle('items.{{ $index }}.priority')">
                                            <option value="" hidden>Select Priority</option>
                                            <option value="Priority">Priority</option>
                                            <option value="Non Priority">Non Priority</option>
                                        </select>
                                        @error("items.$index.priority")
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif

                                    <div class="col-md-2">
                                        <label class="form-label"><strong>Item Status</strong></label>
                                        <select class="form-control form-control-sm border border-1"
                                            wire:model="items.{{ $index }}.item_status"
                                            wire:change="validateSingle('items.{{ $index }}.item_status')">
                                            <option value="" hidden>Select Item Status</option>
                                            <option value="Process">Process</option>
                                            <option value="Hold">Hold</option>
                                        </select>
                                        @error("items.$index.item_status")
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label"><strong>Remarks</strong></label>
                                        <textarea type="text" wire:model="items.{{ $index }}.remarks"  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'" style="resize:none;"
                                            class="form-control form-control-sm border border-1 customer_input"
                                            placeholder="Enter Product Remarks" wire:keydown.enter.prevent></textarea>
                                        @error("items.".$index.".remarks")
                                        <div class="text-danger error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endif
                                @endif
                            </div>

                            {{-- Append Catalogue,Page Number,Page Item data --}}
                            @if(isset($this->items[$index]['product_id']) && $items[$index]['collection'] == 1)
                            <div class="row">
                                <div class="col-12 mt-3">
                                    {{-- First Block: Basic Details (Delivery Date, Fittings, Priority, Status) --}}
                                     @if(isset($items[$index]['collection']) && $items[$index]['collection'] == 1)
                                        <div class="row mb-3">
                                            {{--Delivery Date--}}
                                            <div class="col-md-3 tooltip-wrapper">
                                                <label for="">Delivery Date</label>
                                                <input type="month" class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{$index}}.expected_delivery_date"
                                                    min="{{ \Carbon\Carbon::today()->format('Y-m') }}"
                                                    wire:change="validateSingle('items.{{ $index }}.expected_delivery_date')"
                                                    wire:keydown.enter.prevent>
                                                @error("items.$index.expected_delivery_date")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
    
                                            {{-- Fittings --}}
                                            <div class="col-md-3 tooltip-wrapper">
                                                <label class="form-label"><strong>Fittings</strong></label>
                                                <select class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{ $index }}.fitting"
                                                    wire:change="validateSingle('items.{{ $index }}.fitting')">
                                                    <option value="" hidden>Select Fitting</option>
                                                    <option value="Regular Fit">Regular Fit</option>
                                                    <option value="Slim Fit">Slim Fit</option>
                                                    <option value="Loose Fit">Loose Fit</option>
                                                </select>
                                                @error("items.$index.fitting")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            @if ($priority_level)
                                            {{-- Priority Level --}}
                                            <div class="col-md-3 tooltip-wrapper">
                                                <label class="form-label"><strong>Priority Level</strong></label>
                                                <select class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{ $index }}.priority"
                                                    wire:change="validateSingle('items.{{ $index }}.priority')">
                                                    <option value="" hidden>Select Priority</option>
                                                    <option value="Priority">Priority</option>
                                                    <option value="Non Priority">Non Priority</option>
                                                </select>
                                                @error("items.$index.priority")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            @endif
                                            
                                            <div class="col-md-3 tooltip-wrapper">
                                                <label class="form-label"><strong>Item Status</strong></label>
                                                <select class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{ $index }}.item_status"
                                                    wire:change="validateSingle('items.{{ $index }}.priority')">
                                                    <option value="" hidden>Select Item Status</option>
                                                    <option value="Process">Process</option>
                                                    <option value="Hold">Hold</option>
                                                </select>
                                                @error("items.$index.item_status")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                               
                                            </div>
                                        </div>
                                    @endif
                                     {{---------First Block : End-------------}}
                                     
                                      {{-- Second Block: Catalogue, Page Number, Page Item --}}
                                      
                                    <div class="row g-3">
                                        {{-- Catalogue --}}
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"><strong>Catalogue</strong></label>
                                            <select wire:model="items.{{ $index }}.selectedCatalogue"
                                                class="form-control form-control-sm border border-1 @error('items.'.$index.'.selectedCatalogue') border-danger @enderror"
                                                wire:change="SelectedCatalogue($event.target.value, {{ $index }})">
                                                <option value="" selected hidden>Select Catalogue</option>
                                                @foreach($catalogues[$index] ?? [] as $id => $title)
                                                <option value="{{ $id }}">{{ $title }}
                                                    {{-- @if(isset($maxPages[$index][$id]) ) --}}
                                                    @if(($title !== 'No Catalogue Images') &&
                                                    isset($maxPages[$index][$id]))
                                                    (1 - {{ $maxPages[$index][$id] }})
                                                    @endif
                                                </option>
                                                @endforeach
                                            </select>
                                            @error("items." .$index. ".selectedCatalogue")
                                            <div class="text-danger error-message">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Page number --}}
                                        <div class="mb-3 col-md-3">
                                            <label class="form-label"><strong>Page Number</strong></label>
                                            <input type="number" wire:model="items.{{$index}}.page_number"
                                                wire:keyup="validatePageNumber($event.target.value,{{ $index }})"
                                                id="page_number"
                                                class="form-control form-control-sm border border-2 @error('items.'.$index.'.page_number') border-danger @enderror"
                                                min="1"
                                                max="{{ isset($items[$index]['selectedCatalogue']) && isset($maxPages[$index][$items[$index]['selectedCatalogue']]) ? $maxPages[$index][$items[$index]['selectedCatalogue']] : '' }}"
                                                wire:keydown.enter.prevent 
                                                {{-- Disable if selectedCatalogue points
                                                to "No Catalogue Images" --}}
                                                @if(isset($catalogues[$index][$items[$index]['selectedCatalogue']]) &&
                                                $catalogues[$index][$items[$index]['selectedCatalogue']]==='No Catalogue Images'
                                                ) disabled @endif>
                                            @error("items.".$index.".page_number")
                                            <div class="text-danger error-message">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Page Item --}}
                                        <div class="mb-3 col-md-5">
                                            @if(isset($catalogue_page_item) && !empty($catalogue_page_item[$index]))
                                            <label class="form-label"><strong>Page Item</strong></label>
                                            
                                            <select wire:model="items.{{$index}}.page_item"
                                                class="form-control form-control-sm border border-2 @error('items.'.$index.'.page_item') border-danger @enderror"
                                                 {{-- Disable when "No Catalogue Images" is selected --}}
                                                @if(isset($catalogues[$index][$items[$index]['selectedCatalogue']]) && 
                                                    $catalogues[$index][$items[$index]['selectedCatalogue']] === 'No Catalogue Images')
                                                    disabled
                                                @endif
                                                >
                                                <option value="" selected hidden>Select Page Item</option>
                                                @foreach($pageItems[$index] ?? [] as $id => $item)
                                                <option value="{{ $item->catalog_item  }}">
                                                    {{ $item->catalog_item }}
                                                </option>
                                                @endforeach
                                            </select>

                                            @error("items.".$index.".page_item")
                                            <div class="text-danger error-message">{{ $message }}</div>
                                            @enderror
                                            @endif
                                        </div>
                                        
                                    </div>
                                    
                                     {{---------Second Block : End-------------}}
                                     
                                     {{-- Third Block: Extra Measurement & Uploads --}}
                                     
                                        {{--Extra Measurement For Suit,Trouser,Jacket Section Start--}}
                                        @php
                                            $extras = $extra_measurement[$index] ?? [];
                                        @endphp
                                        @if(count($extras)>0)
                                            <div class="col-12 my-3">
                                                    <div class="row g-3">
                                                         {{-- ================= JACKET DETAILING SECTION ================= --}}
                                                    @if(in_array('mens_jacket_suit', $extras) || in_array('ladies_jacket_suit', $extras))
                                                        <div class="col-12 col-xl-6">
                                                            <div class="card border border-1 shadow-sm">
                                                               <div class="card-header bg-light">
                                                                <h5 class="card-title mb-0 text-primary"><strong>Jacket Detailing</strong></h5>
                                                               </div>
                                                                <div class="card-body">
                                                                   <div class="row g-3">
                                                                        {{-- ================= MEN JACKET ================= --}}
                                                                        @if(in_array('mens_jacket_suit',$extras))
                                                                        <div class="col-md-3 tooltip-wrapper">
                                                                            <label class="form-label"><strong>Vents</strong></label>
                                                                            <select class="form-control form-control-sm border border-1"
                                                                                wire:model="items.{{ $index }}.vents"
                                                                                wire:change="validateSingle('items.{{ $index }}.vents')">
                                                                                <option value="" hidden>Select Vents</option>
                                                                                <option value="1 Vent">1 Vent</option>
                                                                                <option value="2 Vents">2 Vents</option>
                                                                            </select>
                                                                            @error("items.$index.vents")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label><strong>Client Name</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:model="items.{{ $index }}.client_name_required"
                                                                                wire:change="validateSingle('items.{{ $index }}.client_name_required')">
                                                                                <option value="" hidden>Select</option>
                                                                                <option value="Yes">Yes</option>
                                                                                <option value="No">No</option>
                                                                            </select>
                                                                            @error("items.$index.client_name_required")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                
                                                                        @if(!empty($items[$index]['client_name_required']) &&
                                                                        $items[$index]['client_name_required'] == 'Yes')
                                                                        <div class="col-md-3">
                                                                            <label><strong>Name</strong></label>
                                                                            <textarea type="text" class="form-control form-control-sm border border-1" oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'" 
                                                                                style="resize:none;"
                                                                                wire:model="items.{{ $index }}.client_name_place"
                                                                                wire:keydown.enter.prevent></textarea>
                                                                            
                                                                            @error("items.$index.client_name_place")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                       
                                                                        @endif
                                                                        <div class="col-md-3">
                                                                            <label><strong>Shoulder Type</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:model="items.{{ $index }}.shoulder_type"
                                                                                wire:change="validateSingle('items.{{ $index }}.shoulder_type')">
                                                                                <option value="" hidden>Select</option>
                                                                                <option value="Straight">Straight</option>
                                                                                <option value="Normal">Normal</option>
                                                                                <option value="Little Down">Little Down</option>
                                                                                <option value="Down">Down</option>
                                                                            </select>
                                                                            @error("items.$index.shoulder_type")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        {{--Hand Stitching--}}
                                                                        <div class="col-md-3">
                                                                            <label><strong>Hand Stitching</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:init="$set('items.{{ $index }}.mens_hand_stitching', 'No')"
                                                                                wire:model="items.{{ $index }}.mens_hand_stitching"
                                                                                wire:change="validateSingle('items.{{ $index }}.mens_hand_stitching')">
                                                                                <!--<option value="" hidden>Select</option>-->
                                                                                <option value="No">No</option>
                                                                                <option value="Semi">Semi</option>
                                                                                <option value="Full">Full</option>
                                                                            </select>
                                                                            @error("items.$index.mens_hand_stitching")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        
                                                                        
                                                                        @endif
                                
                                                                        {{-- ================= LADIES JACKET ================= --}}
                                                                        @if(in_array('ladies_jacket_suit',$extras))
                                                                            <div class="col-md-3">
                                                                                <label><strong>Shoulder Type</strong></label>
                                                                                <select class="form-control form-control-sm"
                                                                                    wire:model="items.{{ $index }}.shoulder_type"
                                                                                    wire:change="validateSingle('items.{{ $index }}.shoulder_type')">
                                                                                    <option value="" hidden>Select</option>
                                                                                    <option value="Straight">Straight</option>
                                                                                    <option value="Normal">Normal</option>
                                                                                    <option value="Little Down">Little Down</option>
                                                                                    <option value="Down">Down</option>
                                                                                </select>
                                                                                @error("items.$index.shoulder_type")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                            <!-- Vents Required -->
                                                                            <div class="col-md-3 tooltip-wrapper">
                                                                                <label class="form-label"><strong>Vents Required?</strong></label>
                                                                                <select class="form-control form-control-sm border border-1"
                                                                                    wire:model="items.{{ $index }}.vents_required"
                                                                                    wire:change="validateSingle('items.{{ $index }}.vents_required')">
                                                                                    <option value="" hidden>Select Option</option>
                                                                                    <option value="Yes">Yes</option>
                                                                                    <option value="No">No</option>
                                                                                </select>
                                                                                @error("items.$index.vents_required")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                    
                                                                            </div>
                                
                                                                            <!-- Number of Vents (only if required) -->
                                                                            @if(!empty($items[$index]['vents_required']) &&
                                                                            $items[$index]['vents_required'] == 'Yes')
                                                                            <div class="col-md-3 tooltip-wrapper">
                                                                                <label class="form-label"><strong>How Many Vents?</strong></label>
                                                                                <select class="form-control form-control-sm border border-1"
                                                                                    wire:model="items.{{ $index }}.vents_count"
                                                                                    wire:change="validateSingle('items.{{ $index }}.vents_count')">
                                                                                    {{-- <option value="" hidden>Select Count</option> --}}
                                                                                    <option value="1" selected>1 Vent</option>
                                                                                    <option value="2">2 Vents</option>
                                                                                </select>
                                                                                @error("items.$index.vents_count")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                    
                                                                            </div>
                                                                            @endif
                                                                            <div class="col-md-3">
                                                                                <label><strong>Client Name</strong></label>
                                                                                <select class="form-control form-control-sm"
                                                                                    wire:model="items.{{ $index }}.client_name_required"
                                                                                    wire:change="validateSingle('items.{{ $index }}.client_name_required')">
                                                                                    <option value="" hidden>Select</option>
                                                                                    <option value="Yes">Yes</option>
                                                                                    <option value="No">No</option>
                                                                                </select>
                                                                                @error("items.$index.client_name_required")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                
                                                                            @if(!empty($items[$index]['client_name_required']) &&
                                                                            $items[$index]['client_name_required'] == 'Yes')
                                                                            <div class="col-md-3">
                                                                                <label><strong>Name</strong></label>
                                                                                <input type="text" class="form-control form-control-sm border border-1"
                                                                                    wire:model="items.{{ $index }}.client_name_place"
                                                                                    wire:keydown.enter.prevent>
                                                                               
                                                                                @error("items.$index.client_name_place")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                            @endif
                                                                            
                                                                            <div class="col-md-3">
                                                                                <label><strong>Hand Stitching</strong></label>
                                                                                <select class="form-control form-control-sm"
                                                                                    wire:init="$set('items.{{ $index }}.ladies_hand_stitching', 'No')"
                                                                                    wire:model="items.{{ $index }}.ladies_hand_stitching"
                                                                                    wire:change="validateSingle('items.{{ $index }}.ladies_hand_stitching')">
                                                                                    <!--<option value="" hidden>Select</option>-->
                                                                                    <option value="No">No</option>
                                                                                    <option value="Semi">Semi</option>
                                                                                    <option value="Full">Full</option>
                                                                                </select>
                                                                                @error("items.$index.ladies_hand_stitching")
                                                                                <div class="text-danger">{{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                
                                                        {{-- ================= TROUSER ================= --}}
                                                    @if(in_array('trouser', $extras))
                                                    <div class="col-12 col-xl-6">
                                                            <div class="card border border-1 shadow-sm">
                                                                <div class="card-header bg-light">
                                                                    <h5 class="card-title mb-0 text-success"><strong>Trouser Detailing</strong></h5>
                                                                </div>
                                                                <div class="card-body">
                                                                   <div class="row g-3">
                                                                    <!-- Fold Cuff -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Fold Cuff</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:init="$set('items.{{ $index }}.fold_cuff_required', 'No')"
                                                                            wire:model="items.{{ $index }}.fold_cuff_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.fold_cuff_required')">
                                                                            <!--<option value="" hidden>Select Option</option>-->
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                            <option value="Customized">Customized</option>
                                                                        </select>
                                                                        @error("items.$index.fold_cuff_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                            
                                                                       
                                                                    </div>
                            
                                                                    @if(!empty($items[$index]['fold_cuff_required']) &&
                                                                    $items[$index]['fold_cuff_required'] == 'Customized')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Fold Cuff (cm)</strong></label>
                                                                        <input type="number" min="1"
                                                                            class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.fold_cuff_size"
                                                                            wire:change="validateSingle('items.{{ $index }}.fold_cuff_size')"
                                                                            wire:keydown.enter.prevent>
                                                                        @error("items.$index.fold_cuff_size")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                       
                                                                    </div>
                                                                    @endif
                            
                                                                    <!-- Pleats -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Pleats</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:init="$set('items.{{ $index }}.pleats_required', 'No')"
                                                                            wire:model="items.{{ $index }}.pleats_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.pleats_required')">
                                                                            <!--<option value="" hidden>Select Option</option>-->
                                                                            <option value="No">No</option>
                                                                            <option value="1">1</option>
                                                                            <option value="1(Centre Crease)">1(Centre Crease)</option>
                                                                            <option value="2">2</option>
                                                                        </select>
                                                                        @error("items.$index.pleats_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        {{-- <span class="tooltip-text">Select if pleats are needed.</span> --}}
                                                                    </div>
                            
                                                                   {{-- @if(!empty($items[$index]['pleats_required']) &&
                                                                    $items[$index]['pleats_required'] == 'Yes')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>How Many Pleats?</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.pleats_count"
                                                                            wire:change="validateSingle('items.{{ $index }}.pleats_count')">
                                                                            <option value="" hidden>Select Count</option>
                                                                            <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                            <option value="3">3</option>
                                                                            <option value="4">4</option>
                                                                            <option value="5">5</option>
                                                                        </select>
                                                                        @error("items.$index.pleats_count")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    @endif --}}
                            
                                                                    <!-- Back Pocket -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Back Pocket</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:init="$set('items.{{ $index }}.back_pocket_required', '2')"
                                                                            wire:model="items.{{ $index }}.back_pocket_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.back_pocket_required')">
                                                                            <!--<option value="" hidden>Select Option</option>-->
                                                                            <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.back_pocket_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                       
                                                                    </div>
                            
                                                                 {{--   @if(!empty($items[$index]['back_pocket_required']) &&
                                                                    $items[$index]['back_pocket_required'] == 'Yes')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>How Many Pockets?</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.back_pocket_count"
                                                                            wire:change="validateSingle('items.{{ $index }}.back_pocket_count')">
                                                                            <option value="" hidden>Select Count</option>
                                                                            <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                        </select>
                                                                        @error("items.$index.back_pocket_count")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    @endif --}}
                            
                                                                    <!-- Adjustable Belt -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Adjustable Belt</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.adjustable_belt"
                                                                            wire:change="validateSingle('items.{{ $index }}.adjustable_belt')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.adjustable_belt")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    <!-- Suspender Button -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Suspender Buttons</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.suspender_button"
                                                                            wire:change="validateSingle('items.{{ $index }}.suspender_button')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.suspender_button")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                       
                                                                    </div>
                            
                                                                    <!-- Trouser Position -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Trouser Position</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.trouser_position"
                                                                            wire:change="validateSingle('items.{{ $index }}.trouser_position')">
                                                                            <option value="" hidden>Select Position</option>
                                                                            <option value="High Waist">High Waist</option>
                                                                            <option value="Normal">Normal</option>
                                                                            <option value="Low Waist">Low Waist</option>
                                                                        </select>
                                                                        @error("items.$index.trouser_position")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        {{-- <span class="tooltip-text">Select waist position for trousers.</span>
                                                                        --}}
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                        
                                                        
                                                          {{-- ================= SHIRT ================= --}}
                                                        @if(in_array('shirt', $extras))
                                                    <div class="col-12 mb-4">
                                                        <div class="card border border-1 shadow-sm">
                                                            <div class="card-header bg-light">
                                                                <h5 class="card-title mb-0 text-info"><strong>Shirt Detailing</strong></h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row g-3">
                                                                    <!-- Sleeves -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Sleeves</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.sleeves"
                                                                            wire:change="validateSingle('items.{{ $index }}.sleeves')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="L/S">Long Sleeve (L/S)</option>
                                                                            <option value="H/S">Half Sleeve (H/S)</option>
                                                                        </select>
                                                                        @error("items.$index.sleeves")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    <!-- Collar -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Collar</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.collar"
                                                                            wire:change="validateSingle('items.{{ $index }}.collar')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="Normal">Normal Collar</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                        @error("items.$index.collar")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    @if(!empty($items[$index]['collar']) && $items[$index]['collar'] == 'Other')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Collar Style</strong></label>
                                                                        <textarea type="text" class="form-control form-control-sm border border-1"
                                                                            oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'"
                                                                            style="resize:none;"
                                                                            wire:model="items.{{ $index }}.collar_style"
                                                                            wire:change="validateSingle('items.{{ $index }}.collar_style')"
                                                                            placeholder="Enter Collar Style" wire:keydown.enter.prevent></textarea>
                                                                        @error("items.$index.collar_style")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    @endif
                            
                                                                    <!-- Pocket -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Pocket</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.pocket"
                                                                            wire:change="validateSingle('items.{{ $index }}.pocket')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="With Pocket">With Pocket</option>
                                                                            <option value="Without Pocket">Without Pocket</option>
                                                                        </select>
                                                                        @error("items.$index.pocket")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    <!-- Cuffs -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Cuffs</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.cuffs"
                                                                            wire:change="validateSingle('items.{{ $index }}.cuffs')">
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="Regular">Regular Cuffs</option>
                                                                            <option value="French">French Fold Cuffs</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                        @error("items.$index.cuffs")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    @if(!empty($items[$index]['cuffs']) && $items[$index]['cuffs'] == 'Other')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Cuff Style</strong></label>
                                                                        <input type="text" class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.cuff_style"
                                                                            wire:change="validateSingle('items.{{ $index }}.cuff_style')"
                                                                            placeholder="Enter Cuff Style">
                                                                        @error("items.$index.cuff_style")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    @endif
                                                                    <div class="col-md-3">
                                                                        <label><strong>Client Name</strong></label>
                                                                        <select class="form-control form-control-sm"
                                                                            wire:model="items.{{ $index }}.client_name_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.client_name_required')">
                                                                            <option value="" hidden>Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.client_name_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                            
                                                                    @if(!empty($items[$index]['client_name_required']) &&
                                                                    $items[$index]['client_name_required'] == 'Yes')
                                                                    <div class="col-md-3">
                                                                        <label><strong>Name</strong></label>
                                                                        <input type="text" class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.client_name_place"
                                                                            wire:keydown.enter.prevent>
                                                                       
                                                                        @error("items.$index.client_name_place")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                            <label><strong>Options</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:model="items.{{ $index }}.client_name_options"
                                                                                wire:change="validateSingle('items.{{ $index }}.client_name_options')">
                                                                                <option value="" hidden>Select</option>
                                                                                <option value="Cuffs">Cuffs</option>
                                                                                <option value="Pocket">Pocket</option>
                                                                                <option value="Pocket Space">Pocket Space</option>
                                                                                <option value="Col">Col</option>
                                                                            </select>
                                                                            
                                                                            @error("items.$index.client_name_options")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            </div>
                                        @endif
                                        {{--Extra Measurement For Suit,Trouser,Jacket Section End--}}
                                        
                                        {{--Upload Images,Upload Voice,Start Recording,Stop Recording Section Start--}}
                                        <div class="row">
                                            {{-- Image Upload Section --}}
                                            <div class="mb-3 col-12">
                                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                                    {{-- Image Preview on Left --}}
                                                    @if (!empty($imageUploads[$index]))
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($imageUploads[$index] as $imgIndex => $img)
                                                        <div style="position: relative; width: 70px;">
                                                            @if (is_object($img) && method_exists($img, 'temporaryUrl'))
                                                            <img src="{{ $img->temporaryUrl() }}" class="img-thumbnail"
                                                                style="width: 100%;" />
                                                                @elseif (is_string($img))
                                                                <!-- Restored from Draft (URL string) -->
                                                                <img src="{{ $img }}" 
                                                                     class="img-thumbnail" 
                                                                     style="width: 100%; height: 70px; object-fit: cover;" />
                                                            @endif
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger rounded-circle p-1 position-absolute top-0 end-0"
                                                                style="width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center;"
                                                                wire:click="removeUploadedImage({{ $index }}, {{ $imgIndex }})">
                                                                &times;
                                                            </button>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
    
                                                    {{-- Upload Button on Right --}}
                                                    <div class="ms-auto text-end">
                                                        <button type="button" class="btn btn-cta btn-sm"
                                                            onclick="document.getElementById('catalog-upload-{{ $index }}').click()">
                                                            <i class="material-icons text-white"
                                                                style="font-size: 15px;">add</i>
                                                            Upload Images
                                                        </button>
                                                        <input type="file" id="catalog-upload-{{ $index }}" multiple
                                                            wire:model="newUploads.{{ $index }}" accept="image/*"
                                                            class="d-none" />
                                                        @error('imageUploads.*')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
    
                                            {{-- Voice Upload Section --}}
                                            <div class="mb-3 col-12">
                                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                                    {{-- Voice Preview on Left --}}
                                                    @if (!empty($voiceUploads[$index]))
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($voiceUploads[$index] as $voiceIndex => $voice)
                                                        <div style="width: 150px; position: relative;">
                                                            @if (is_object($voice) && method_exists($voice, 'temporaryUrl'))
                                                            <audio controls style="width: 100%;">
                                                                <source src="{{ $voice->temporaryUrl() }}"
                                                                    type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                            @elseif (is_string($voice))
                                                            <audio controls style="width: 100%;">
                                                                    <source src="{{ $voice }}" type="audio/mpeg">
                                                                </audio>
                                                            @endif
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger rounded-circle p-1 position-absolute top-0 end-0"
                                                                style="width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center;"
                                                                wire:click="removeUploadedVoice({{ $index }}, {{ $voiceIndex }})">
                                                                &times;
                                                            </button>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
    
                                                    {{-- Upload Button on Right --}}
                                                    <div class="ms-auto text-end d-flex align-items-center gap-3 flex-wrap justify-content-end">
                                                        <!-- Upload Voice Button -->
                                                        <button type="button" class="btn btn-cta btn-sm"
                                                            onclick="document.getElementById('voice-upload-{{ $index }}').click()">
                                                            <i class="material-icons text-white"
                                                                style="font-size: 15px;">mic</i>
                                                            Upload Voice
                                                        </button>
    
                                                        <!-- OR separator -->
                                                        <span class="fw-bold text-muted">OR</span>
    
                                                        <!-- Start / Stop Buttons -->
                                                        <div class="ms-auto text-end d-flex gap-2">
                                                            <button type="button" class="btn btn-cta btn-sm"
                                                                onclick="startRecording({{ $index }});"
                                                                id="startBtn_{{ $index }}">
                                                                Start Recording
                                                                <i class="material-icons text-white"
                                                                    style="font-size: 15px;">record_voice_over</i>
                                                            </button>
                                                            <button type="button" class="btn btn-cta btn-sm"
                                                                onclick="stopRecording({{ $index }});"
                                                                id="stopBtn_{{ $index }}" disabled>
                                                                Stop Recording
                                                                <i class="material-icons text-white"
                                                                    style="font-size: 15px;">stop_circle</i>
                                                            </button>
                                                        </div>
                                                    </div>
    
                                                    <div class="ms-auto text-end">
    
                                                        <input type="file" id="voice-upload-{{ $index }}" multiple
                                                            wire:model="voiceUploads.{{ $index }}" accept="audio/*"
                                                            class="d-none" />
                                                        @error('voiceUploads.*')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{--Upload Images,Upload Voice,Start Recording,Stop Recording Section End--}}
                                        
                                    {{----------Third Block: End-----------}}  
                                    
                                        {{-- Fourth Block: Remarks --}}
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label"><strong>Remarks</strong></label>
                                                <textarea type="text" wire:model="items.{{ $index }}.remarks"
                                                    class="form-control form-control-sm border border-1 customer_input" oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'" style="resize:none;"
                                                    placeholder="Enter Product Remarks"
                                                    wire:keydown.enter.prevent></textarea>
                                                @error("items.".$index.".remarks")
                                                <div class="text-danger error-message">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        {{----------Fourth Block: End-----------}}
                                    
                                </div>
                            </div>
                            @endif
                            @endforeach
                         
                            <!-- Add Item Button -->
                            <div class="row align-items-end my-4">
                                <div class="col-md-7 col-12"></div>
                                <div class="col-md-5 col-12">
                                    <table>
                                        <tr>
                                            <td colspan="2">
                                                <table style="width:100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="text-align: center;">
                                                                @if (session()->has('errorAmount'))
                                                                <div class="alert alert-danger">
                                                                    {{ session('errorAmount') }}
                                                                </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: end;">
                                                                <button type="button" class="btn btn-success btn-sm"
                                                                    wire:click="addItem">
                                                                    <i class="material-icons text-white"
                                                                        style="font-size: 15px;">add</i>
                                                                    Add Item
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="w-70"><label class="form-label"><strong>Air Mail</strong></label>
                                            </td>
                                            <td>
                                                <!-- Sub Total -->
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="air_mail" wire:keyup="updateBillingAmount"
                                                    placeholder="Enter air mail cost" wire:keydown.enter.prevent>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="w-70"><label class="form-label"><strong>Total
                                                        Amount</strong></label>
                                            </td>
                                            <td>
                                                <!-- Sub Total -->
                                                <input type="text" class="form-control form-control-sm text-center"
                                                    wire:model="billing_amount" disabled
                                                    value="{{ number_format($billing_amount, 2) }}">
                                            </td>
                                        </tr>


                                    </table>
                                </div>
                                <div class="col-md-4 col-12"></div>
                            </div>
                            
                               {{--Append the Unified Action Button & Layout View--}}
                              <div class="card my-4 shadow-sm border border-secondary" style="border-radius: 8px; overflow: hidden;">
                                <div class="card-header bg-dark d-flex justify-content-between align-items-center py-3">
                                    <h6 class="mb-0 text-primary font-weight-bold d-flex align-items-center">
                                        <i class="material-icons me-2 text-warning" style="font-size: 22px;">straighten</i> 
                                        Measurement Entry Layout Control Panel
                                    </h6>
                                    <button type="button" class="btn btn-sm {{ $isUnifiedViewActive ? 'btn-warning' : 'btn-outline-primary' }} mb-0 px-3 shadow-none text-uppercase" 
                                            wire:click="toggleUnifiedMeasurementView">
                                        {{ $isUnifiedViewActive ? 'Hide Measurement' : 'Show Measurement' }}
                                    </button>
                                </div>
                                
                                @if($isUnifiedViewActive)
                                    <div class="card-body bg-white p-4">
                                        @if(empty($unifiedMeasurements))
                                            <div class="text-center py-4">
                                                <span class="material-icons text-muted" style="font-size: 48px;">playlist_add</span>
                                                <p class="text-muted mt-2 small font-weight-bold">No sizing items found. Please select a valid product row above.</p>
                                            </div>
                                        @else
                                         
                                            
                                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                                                @foreach($unifiedMeasurements as $loopKey => $uField)
                                                    <div class="col" wire:key="unified-field-matrix-{{ $loopKey }}">
                                                        <div class="border rounded p-2 bg-light shadow-2xs h-100 d-flex flex-column justify-content-between" style="border-color: #dee2e6; border-radius: 6px;">
                                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                                <label class="mb-0 font-weight-bold text-dark small text-uppercase" style="max-width: 85%; line-height: 1.2;">
                                                                    {{ $uField['title'] }} 
                                                                    <span class="text-danger font-weight-bolder">[{{ $uField['short_code'] }}]</span>
                                                                </label>
                                                                 <span class="message-icon" style="cursor:pointer; font-size:14px; opacity: 0.7;" wire:click="$toggle('unifiedMeasurements.{{ $loopKey }}.show_remarks')">
                                                                    <i class="fas fa-comment-alt"></i>
                                                                </span>
                                                            </div>
                                                            
                                                            <input type="text" 
                                                                   wire:model.live="unifiedMeasurements.{{ $loopKey }}.value" required
                                                                   class="form-control form-control-sm text-center border text-dark font-weight-bold bg-white"
                                                                   style="border-color: #ced4da; border-radius: 4px; font-size: 0.95rem; height: 36px;"
                                                                   wire:keydown.enter.prevent>
                                                                   
                                                            @error("unifiedMeasurements.{$loopKey}.value")
                                                                <span class="text-danger small mt-1" style="font-size: 0.7rem;">{{ $message }}</span>
                                                            @enderror
                                                                   
                                                            @if(!empty($uField['show_remarks']))
                                                                <div class="mt-2">
                                                                    <textarea class="form-control form-control-sm border p-2" 
                                                                              rows="2" 
                                                                              placeholder="Adjustment specification remarks..."
                                                                              wire:model.live="unifiedMeasurements.{{ $loopKey }}.remarks"
                                                                              wire:keydown.enter.prevent
                                                                              style="font-size: 0.75rem; border-color: #adb5bd; border-radius: 4px; resize: none; min-height: 45px;"></textarea>
                                                                </div>
                                                                @error("unifiedMeasurements.{$loopKey}.remarks")
                                                                    <span class="text-danger small mt-1" style="font-size: 0.7rem;">Remarks are required.</span>
                                                                @enderror
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                              {{--Append the Unified Action Button & Layout View End--}}
                        </div>

                        <div class="d-flex justify-content-end align-items-center mb-3">
                            @if($activeTab>1)
                            <button type="button" class="btn btn-dark mx-2 btn-sm"
                                wire:click="TabChange({{$activeTab-1}})"><i
                                    class="material-icons text-white">chevron_left</i>Previous</button>
                            <button type="submit" class="btn btn-primary mx-2 btn-sm"><i
                                    class="material-icons text-white">add</i>Generate Order</button>
                            @endif
                            @if($activeTab==1)
                            <button type="button" id="nextTab" class="btn btn-sm btn-success mx-2"
                                wire:click="TabChange({{$activeTab+1}})">Next<i
                                    class="material-icons text-white">chevron_right</i></button>
                            @endif

                        </div>
            </form>
            <!-- Tabs content -->
        </div>
    </div>
</div>
@push('js')

<!--Auto Save Code-->
   
<script>
   // Auto Save Code
    let autoSaveInterval = null;
    let isLoggingOut = false;
    
    // function startAutoSave() {
    //     if (autoSaveInterval) clearInterval(autoSaveInterval);
    
    //     autoSaveInterval = setInterval(() => {
    //         if (document.visibilityState === 'visible' && !isLoggingOut) {
    //             // Check if Livewire component is still alive before saving
    //             try {
    //                 if (window.Livewire && @this) {
    //                     @this.saveDraft().catch(() => {
    //                         // Silently fail - session may have expired
    //                         clearInterval(autoSaveInterval);
    //                     });
    //                 }
    //             } catch(e) {
    //                 clearInterval(autoSaveInterval);
    //             }
    //         }
    //     }, 30000); // increase to 30 seconds to reduce server load
    // }
    function startAutoSave() {
        if (autoSaveInterval) clearInterval(autoSaveInterval);

        autoSaveInterval = setInterval(() => {
            if (document.visibilityState === 'visible') {
                @this.saveDraft();
            }
        }, 500);
    }
    
    window.addEventListener('start-auto-save', startAutoSave);
    
    // CRITICAL FIX: Detect logout clicks and stop auto-save
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a[href*="logout"], form[action*="logout"] button, button[onclick*="logout"]');
        if (target) {
            isLoggingOut = true;
            if (autoSaveInterval) clearInterval(autoSaveInterval);
        }
    });
    
    // Also intercept any form submission that looks like logout
    document.addEventListener('submit', function(e) {
        if (e.target.action && e.target.action.includes('logout')) {
            isLoggingOut = true;
            if (autoSaveInterval) clearInterval(autoSaveInterval);
        }
    });
    
    window.addEventListener('beforeunload', () => {
        if (autoSaveInterval) clearInterval(autoSaveInterval);
        // Do NOT fire saveDraft on beforeunload - it blocks navigation and breaks logout
    });
    
    window.addEventListener('unload', () => {
        if (autoSaveInterval) clearInterval(autoSaveInterval);
    });
    
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            if (autoSaveInterval) clearInterval(autoSaveInterval);
        } else if (document.visibilityState === 'visible' && !isLoggingOut) {
            startAutoSave();
        }
    });
    
    // Handle Livewire page expiry gracefully
    window.addEventListener('livewire:failed', () => {
        if (autoSaveInterval) clearInterval(autoSaveInterval);
        isLoggingOut = true; // stop all further saves
    });
    // Auto Save Code End
</script>
<!--Auto Save Code End -->
<script>
    const mediaRecorders = {};
    const audioChunksMap = {};

    //  Assigns a file to a hidden file input so Livewire can pick it up
    function assignFileToInput(index, file) {
        const dt = new DataTransfer();
        dt.items.add(file);

        const input = document.getElementById(`voice-upload-${index}`);
        input.files = dt.files;

        // Trigger Livewire to pick it up
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);

        console.log(` File assigned to input #voice-upload-${index}`);
    }

    // Start recording
    async function startRecording(index) {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const mediaRecorder = new MediaRecorder(stream);
        const chunks = [];

        mediaRecorder.ondataavailable = (e) => {
            chunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(chunks, { type: 'audio/webm' });
            const file = new File([blob], `recording_${index}.webm`, { type: 'audio/webm' });

            assignFileToInput(index, file); //  assign file to Livewire input
        };

        mediaRecorders[index] = mediaRecorder;
        audioChunksMap[index] = chunks;

        mediaRecorder.start();

        // Optional UI state
        document.getElementById(`startBtn_${index}`).disabled = true;
        document.getElementById(`stopBtn_${index}`).disabled = false;
    }

    // Stop recording
    function stopRecording(index) {
        if (mediaRecorders[index]) {
            mediaRecorders[index].stop();
        }

        // Optional UI state
        document.getElementById(`startBtn_${index}`).disabled = false;
        document.getElementById(`stopBtn_${index}`).disabled = true;
    }
    function assignFileToInput(index, file) {
    const dt = new DataTransfer();
    dt.items.add(file);

    const input = document.getElementById(`voice-upload-${index}`);
    input.files = dt.files;

    input.dispatchEvent(new Event('change', { bubbles: true }));
}

</script>
<script>
    window.addEventListener('error_message', event => {
        setTimeout(() => {
            let errorElement = document.querySelector(".error-message");
            if (errorElement) {
                errorElement.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        }, 100);
    });

document.addEventListener("DOMContentLoaded", function() {
  document.addEventListener("click", function(event) {
    if (event.target.closest("#nextTab")) {
      setTimeout(function () {
        const scrollContainer = document.querySelector('#sales_order_data'); // Replace with real selector
        if (scrollContainer) {
          scrollContainer.scrollTo({
            top: 0,
            behavior: "smooth"
          });
        } else {
          console.log("Scroll container not found");
        }
      }, 300);
    }
  });
});


 window.addEventListener('open-skip-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('skipModal'));
        myModal.show();
    });
    
    window.addEventListener('hide-skip-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('skipModal'));
        myModal.hide();
    });
    
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.1.6/js/intlTelInput.min.js"></script>
<script>
    let dialCodesCache = null;

        // Load JSON once when page loads
        $.getJSON("{{ asset('assets/js/dial-codes.json') }}", function (data) {
            dialCodesCache = data;

            // Once JSON is loaded, init inputs
            initIntlTelInput("#mobile", "phone", "phone_code");
            initIntlTelInput("#alt_phone_1", "alternative_phone_number_1", "alt_phone_code_1");
            initIntlTelInput("#alt_phone_2", "alternative_phone_number_2", "alt_phone_code_2");
        });
        function loadDialCodes(dialNumber) {
            if (!dialCodesCache) return "cf"; // fallback while JSON not loaded
            return dialCodesCache[dialNumber] || "cf"; // default to cf
        }

        function initIntlTelInput(selector, phoneModel, codeModel, defaultCountry = "cf") {
            var input = $(selector);
            var codeInput = $("#" + codeModel);
            var phoneInput = $("#" + phoneModel);
            var selected_dial_code = codeInput.val(); // only digits
            var selected_phone_number = phoneInput.val(); // only digits
            var defaultCountry = loadDialCodes(selected_dial_code);
            input.intlTelInput({
                initialCountry: defaultCountry,  // Central African Republic by default
                preferredCountries: ["us", "gb", "in", "cf"],
                separateDialCode: true
            });
             input.val(selected_phone_number);
            // On input change (number typing)
            input.on("input", function () {
                let number = input.val().replace(/\D/g, ''); // only digits
                @this.set(phoneModel, number);
            });

            // On country change
            input.on("countrychange", function () {
                let code = "+" + input.intlTelInput("getSelectedCountryData").dialCode;
                @this.set(codeModel, code);
                @this.call('CountryCodeSet', selector, code);
            });

            // Set default on load
            let defaultCode = "+" + input.intlTelInput("getSelectedCountryData").dialCode;
            @this.set(codeModel, defaultCode);
            @this.call('CountryCodeSet', selector, defaultCode,selected_phone_number);
        }
       // Already existing
        window.addEventListener('update_input_max_length', function (event) {
            let itemId = event.detail[0].id;
            let mobile_length = event.detail[0].mobile_length;
            if (itemId && mobile_length) {
                document.querySelector(itemId).setAttribute("maxlength", mobile_length);
            }
        });

        function collectPreferredMap(parentId) {
            let map = {};
            $(`#${parentId} .country-list li.country`).each(function () {
                let dialCode = $(this).data("dial-code");
                let iso2 = $(this).data("country-code");
                map["+" + dialCode] = iso2;
            });

            return map;
        }

        // Example
        $(function () {
            // collect once on load
            window.preferredMap = collectPreferredMap("parent_mobile");
            console.log(window.preferredMap); 
        });

        window.addEventListener('update_input_code_number', function (event) {
            let itemId = event.detail[0].id;
            let dialCode = event.detail[0].dialCode;
            let number = event.detail[0].number || '';
            if (itemId && dialCode) {
                // find corresponding parent wrapper
                let parentId = "";
                if (itemId === "#mobile") {
                    parentId = "#parent_mobile";
                } else if (itemId === "#alt_phone_1") {
                    parentId = "#parent_alt1";
                } else if (itemId === "#alt_phone_2") {
                    parentId = "#parent_alt2";
                }

                if (parentId) {
                    let countryCode = window.preferredMap[dialCode]; // e.g. "in"
                        // console.log("Updating:", countryCode, dialCode);
                    if (countryCode) {
                        // Update flag inside .selected-flag
                        $(`${parentId} .selected-flag .iti-flag`)
                            .attr("class", `iti-flag ${countryCode}`);

                        // Update dial code inside .selected-flag
                        $(`${parentId} .selected-flag .selected-dial-code`).text(dialCode);

                        // Optionally set value to input
                        $(`${itemId}`).val(number);
                    }
                }
            }
        });
</script>
<script>
    window.addEventListener('select-accessories', event => {
        const index = event.detail.index;
        const categoryId = event.detail.categoryId;

        // Set the category in Livewire
        @this.set(`items.${index}.category`, categoryId);

        // Optionally trigger CategoryWiseProduct automatically
        @this.call('CategoryWiseProduct', categoryId, index);
    });
</script>

@endpush