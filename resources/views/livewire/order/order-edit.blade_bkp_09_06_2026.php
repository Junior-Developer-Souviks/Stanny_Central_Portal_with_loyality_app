<div class="container">
     <style>
     .message-icon:hover {
            opacity: 1 !important;
            color: #007bff; /* Optional: adds a blue color on hover */
        }
       .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); /* Dim background overlay */
            z-index: 99999; /* Make sure it sits on top of absolutely everything */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
        }
        
        /* Smooth Circular Spinner Minimalist Design */
        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #e91e63; /* Matches Material Dashboard Pink/Primary */
            width: 50px;
            height: 50px;
            margin-bottom: 15px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style> 
    <section class="admin__title">
        <h5>Update Order <span class="badge bg-success custom_success_badge">{{env('ORDER_PREFIX') .
                $order_number}}</span></h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Sales Management</li>
            @if(!empty($orders))
            <li><a href="{{route('admin.order.edit',$orders->id)}}">Update Order</a></li>
            @endif
            <li class="back-button">
                @if($activeTab==1)
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0"
                    href="{{route('admin.order.index')}}" role="button">
                    <i class="material-icons" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1">Back</span>
                </a>
                @endif
            </li>
        </ul>
    </section>
   
    <div class="card my-4">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
             
            </div>
        </div>
        <div class="card-body" id="sales_order_data">
            <form wire:submit.prevent="update">
                <div class="{{$activeTab==1?" d-block":"d-none"}}" id="tab1">
                     {{-- checkbox section --}}
                    <div class="mb-2">
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
                        </div>
                    </div>
                    
                    @if ($customerType == 'existing')
                        <!-- Search Label and Select2 -->
                        <div class="col-md-6 mt-2 mb-2">
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
                                    
                                        @php
                                            $hasValidCustomer = false;
                                        @endphp
                                    
                                        @foreach ($searchResults as $customer)
                                    
                                            @php
                                                $latestOrder = \App\Models\Order::where('customer_id', $customer->id)
                                                                ->latest()
                                                                ->first();
                                            @endphp
                                    
                                            @if(!$latestOrder)
                                                @php
                                                    $hasValidCustomer = true;
                                                @endphp
                                    
                                                <button class="dropdown-item" type="button"
                                                    wire:click="selectCustomer({{ $customer->id }})">
                                    
                                                    <img src="{{ $customer->profile_image ? asset($customer->profile_image) : asset('assets/img/user.png') }}"
                                                        alt="">
                                    
                                                    {{ ucfirst($customer->prefix . " ". $customer->name) }}
                                    
                                                    ({{ $customer->country_code_phone .' '.$customer->phone }})
                                    
                                                    @if($latestOrder)
                                                        - Order: {{ $latestOrder->order_number }}
                                                    @endif
                                    
                                                </button>
                                            @endif
                                    
                                        @endforeach
                                    
                                        @if(!$hasValidCustomer)
                                            <div class="dropdown-item text-danger">
                                              Order Created But Customer Not Assigned 
                                            </div>
                                        @endif
                                    
                                    </div>
                                    @endif
                            </div>
                        </div>
                        @endif
                    
                    <div class="row d-flex justify-content-between align-items-center mb-2">
                        <!-- Customer Information Badge -->
                        <div class="col-md-4">
                            <h6 class="badge bg-danger custom_danger_badge mb-0">Basic Information</h6>
                        </div>
                        <div class="col-md-8 d-flex justify-content-end gap-3">

                            <div class="section-header-filter">
                                <!-- Search Label -->
                                <label for="searchCustomer" class="form-label mb-0">Business Type:&nbsp;</label>
                                <select wire:model="selectedBusinessType" class="form-select form-control"
                                    aria-label="Default select example">
                                    <option selected hidden>Select Domain</option>
                                    @foreach ($Business_type as $domain)
                                    <option value="{{$domain->id}}">{{$domain->title}}</option>
                                    @endforeach
                                </select>
                                @if(isset($errorMessage['selectedBusinessType']))
                                <div class="text-danger">{{ $errorMessage['selectedBusinessType'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Customer Details -->
                    {{-- <div class="container"> --}}
                        <!-- Customer Details -->
                        <div class="row">
                            <div class="mb-2 col-md-3">
                                <input type="hidden" name="customer_id" wire:model="customer_id">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select wire:model="prefix" class="form-control form-control-sm border border-1"
                                        style="max-width: 60px;">
                                        <option value="" selected hidden>Prefix</option>
                                        @foreach (App\Helpers\Helper::getNamePrefixes() as $prefixOption)
                                        <option value="{{$prefixOption}}">{{ $prefixOption }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="name" id="name"
                                        class="form-control form-control-sm border border-1 p-2 {{ $errorClass['name'] ?? '' }}"
                                        placeholder="Enter Customer Name">
                                </div>
                                @if(isset($errorMessage['name']))
                                <div class="text-danger">{{ $errorMessage['name'] }}</div>
                                @endif
                            </div>

                            <div class="mb-2 col-md-2">
                                <label for="employee_rank" class="form-label"> Rank</label>
                                <input type="text" wire:model="employee_rank" id="employee_rank"
                                    class="form-control form-control-sm border border-1 p-2" placeholder="Enter Rank">
                            </div>

                            <div class="mb-2 col-md-4">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" wire:model="company_name" id="company_name"
                                    class="form-control form-control-sm border border-1 p-2"
                                    placeholder="Enter Company Name">
                            </div>



                            <div class="mb-2 col-md-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" wire:model="email" id="email"
                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['email'] ?? '' }}"
                                    placeholder="Enter Email">
                                @if(isset($errorMessage['email']))
                                <div class="text-danger error-message">{{ $errorMessage['email'] }}</div>
                                @endif
                            </div>



                            <div class="mb-2 col-md-3">
                                <label for="dob" class="form-label">Date Of Birth </label>
                                <input type="date" wire:model="dob" id="dob" max="{{date('Y-m-d')}}"
                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['dob'] ?? '' }}" >
                                    
                            </div>
                            <!-- Phone Number -->
                            <div class="mb-2 col-md-3">
                                <label for="mobile" class="form-label">Phone Number</label>
                                <div class="input-group input-group-sm" id="parent_mobile" wire:ignore>
                                    <input id="mobile" type="tel" class="form-control tel-code-input"
                                        style="width:286px;">
                                    <!-- hidden Livewire bindings -->
                                    <input type="hidden" wire:model="phone_code" id="phone_code">
                                    <input type="hidden" wire:model="phone" id="phone">

                                </div>

                                @if(isset($errorMessage['phone']))
                                <div class="text-danger error-message">{{ $errorMessage['phone'] }}</div>
                                @enderror
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp1" wire:model="isWhatsappPhone">
                                    <label for="is_whatsapp1" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                            <!-- Alternative Phone Number 1 -->
                            <div class="mb-2 col-md-3">
                                <label for="alt_phone_1" class="form-label">Alternative Phone 1</label>
                                <div class="input-group input-group-sm" id="parent_alt_phone_code_1" wire:ignore>
                                    <input id="alt_phone_1" type="tel" class="form-control tel-code-input"
                                        style="width:269px;">
                                    <input type="hidden" wire:model="alt_phone_code_1" id="alt_phone_code_1">
                                    <input type="hidden" wire:model="alternative_phone_number_1"
                                        id="alt_phone_hidden_1">
                                </div>
                                @if(isset($errorMessage['alternative_phone_number_1']))
                                <div class="text-danger error-message">{{ $errorMessage['alternative_phone_number_1'] }}
                                </div>
                                @endif
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp2" wire:model="isWhatsappAlt1">
                                    <label for="is_whatsapp2" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                            <!-- Alternative Phone Number 2 -->
                            <div class="mb-2 col-md-3">
                                <label for="alt_phone_2" class="form-label">Alternative Phone 2</label>
                                <div class="input-group input-group-sm" id="parent_alt_phone_code_2" wire:ignore>
                                    <input id="alt_phone_2" type="tel" class="form-control tel-code-input"
                                        style="width:269px;">
                                    <input type="hidden" wire:model="alt_phone_code_2" id="alt_phone_code_2">
                                    <input type="hidden" wire:model="alternative_phone_number_2"
                                        id="alt_phone_hidden_2">
                                </div>
                                @if(isset($errorMessage['alternative_phone_number_2']))
                                <div class="text-danger error-message">{{ $errorMessage['alternative_phone_number_2'] }}
                                </div>
                                @endif
                                <div class="form-check-label-group">
                                    <input type="checkbox" id="is_whatsapp3" wire:model="isWhatsappAlt2">
                                    <label for="is_whatsapp3" class="form-check-label ms-1">Is Whatsapp</label>
                                </div>
                            </div>

                           
                        </div>



                        <div class="">
                            <div class="">
                                <h6 class="badge bg-danger custom_danger_badge">Address</h6>
                            </div>
                            <div class="pt-0">

                                <div class="admin__content">
                                    {{-- Billing Address --}}
                                    <aside>
                                        <nav class="text-uppercase font-weight-bold">Address</nav>
                                    </aside>
                                    <content>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="billing_address" class="col-form-label"> Address <span
                                                        class="text-danger">*</span>
                                                </label>
                                            </div>
                                            <div class="col-9">
                                                <input wire:model="billing_address" id="billing_address" cols="30"
                                                    rows="3"
                                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['billing_address'] ?? '' }}"
                                                    placeholder="Enter billing address">
                                                @if(isset($errorMessage['billing_address']))
                                                <div class="text-danger">{{ $errorMessage['billing_address'] }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="billing_landmark" class="form-label">Landmark</label>
                                            </div>
                                            <div class="col-9">
                                                <input type="text" wire:model="billing_landmark" id="billing_landmark"
                                                    class="form-control form-control-sm border border-1 p-2"
                                                    placeholder="Enter landmark">
                                            </div>
                                        </div>

                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="billing_city" class="form-label">City <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-9">
                                                <input type="text" wire:model="billing_city" id="billing_city"
                                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['billing_city'] ?? '' }}"
                                                    placeholder="Enter city">
                                                @if(isset($errorMessage['billing_city']))
                                                <div class="text-danger">{{ $errorMessage['billing_city'] }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row mb-2 align-items-center">
                                            <div class="col-3">
                                                <label for="billing_country" class="form-label">Country <span
                                                        class="text-danger">*</span>
                                                </label>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" wire:model="billing_country" id="billing_country"
                                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['billing_country'] ?? '' }}"
                                                    placeholder="Enter country">
                                                @if(isset($errorMessage['billing_country']))
                                                <div class="text-danger">{{ $errorMessage['billing_country'] }}</div>
                                                @endif
                                            </div>
                                            <div class="col-3 text-end">
                                                <label for="billing_pin" class="form-label">Pincode </label>
                                            </div>
                                            <div class="col-3">
                                                <input type="number" wire:model="billing_pin" id="billing_pin"
                                                    class="form-control form-control-sm border border-1 p-2 {{ $errorClass['billing_pin'] ?? '' }}"
                                                    placeholder="Enter PIN">
                                                @if(isset($errorMessage['billing_pin']))
                                                <div class="text-danger">{{ $errorMessage['billing_pin'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </content>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ $activeTab == 2 ? 'd-block' : 'd-none' }}" id="tab2">
                        
                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label"><strong>Customer Name</strong></label>
                                <input type="text" class="form-control form-control-sm" value="{{ $name }}" readonly>
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
                        {{-- Customer Image only for garment --}}
                        @php
                        $hasGarment = collect($items)->contains('selected_collection', 1);
                        
                           $auth = Auth::guard('admin')->user();
    
                            // Check if any item is Approved (or any other logic you need)
                            $isDisabled = collect($items)->contains(function ($item) {
                                return ($item['admin_status'] ?? '') === 'Approved';
                            });
                            
                            // OR: Check logic based on specific user permissions if needed
                            if (!$isDisabled) {
                                $isDisabled = collect($items)->contains(function ($item) use ($auth) {
                                    return ($item['status'] ?? '') === 'Process' && 
                                           ($item['tl_status'] ?? '') === 'Approved' && 
                                           in_array($auth->designation, [2, 4]);
                                });
                            }
                        @endphp

                       @if($hasGarment)
                            
                            
                                <!-- Client Image Upload -->
                                <div class="col-md-3">
                                    <label class="form-label"><strong>Client Profile Image</strong> <span class="text-danger">*</span></label>
                            
                                    <input type="file" wire:model="customer_image" multiple  
                                        class="form-control form-control-sm border border-1 p-2 @error('customer_image') border-danger @enderror" {{ $isDisabled ? 'disabled' : '' }}>
                            
                                    <div wire:loading wire:target="customer_image" class="text-info small mt-1">
                                        Uploading image...
                                    </div>
                            
                                    @error('customer_image')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                            
                                    <div class="mt-2">
                                        <p class="small mb-1 text-bold">Preview</p>
                                        
                                        @if(!empty($customer_image))

                                            @foreach($customer_image as $img)
                                        
                                                @if(is_string($img))
                                                    <img src="{{ asset($img) }}" width="60">
                                                @else
                                                    <img src="{{ $img->temporaryUrl() }}" width="60">
                                                @endif
                                        
                                            @endforeach
                                        
                                        @endif
                                    </div>
                                </div>
                                @endif
                            
                               
                                
                               <!-- Physical Bill Book -->
                                <div class="col-md-3">
                                
                                    <p class="small mb-1 text-bold">Physical Bill Book <span class="text-danger">*</span></p>
                                
                                    <!-- INPUT -->
                                    <div class="mt-2 mb-3">
                                
                                        <input type="file"
                                               class="form-control form-control-sm"
                                               wire:model="physical_order_bill_book_new"
                                               multiple {{ $isDisabled ? 'disabled' : '' }}>
                                
                                    </div>
                                
                                    <!-- LOADING -->
                                    <div wire:loading wire:target="physical_order_bill_book_new"
                                         class="text-info small mt-1 mb-2">
                                
                                        Uploading...
                                
                                    </div>
                                
                                    <!-- ERROR -->
                                    @error('physical_order_bill_book_new')
                                
                                        <span class="text-danger small d-block mb-2">
                                            {{ $message }}
                                        </span>
                                
                                    @enderror
                                
                                
                                    <!-- OLD FILES -->
                                    @if(empty($physical_order_bill_book_new))
                                
                                        @if(!empty($physical_order_bill_book))
                                
                                            <div class="d-flex flex-wrap gap-2">
                                
                                                @foreach($physical_order_bill_book as $bill)
                                
                                                    @php
                                                        $extension = strtolower(pathinfo($bill, PATHINFO_EXTENSION));
                                                    @endphp
                                
                                                    <!-- IMAGE -->
                                                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                                
                                                        <a href="{{ asset($bill) }}" target="_blank">
                                
                                                            <img src="{{ asset($bill) }}"
                                                                 style="width:60px;height:60px;object-fit:cover;"
                                                                 class="img-thumbnail shadow-sm" >
                                
                                                        </a>
                                
                                                    <!-- PDF -->
                                                    @elseif($extension == 'pdf')
                                
                                                        <a href="{{ asset($bill) }}"
                                                           target="_blank"
                                                           class="text-decoration-none">
                                
                                                            <div style="
                                                                width:60px;
                                                                height:60px;
                                                                border:1px solid #ccc;
                                                                display:flex;
                                                                align-items:center;
                                                                justify-content:center;
                                                                background:#f8f9fa;
                                                                font-size:12px;
                                                                font-weight:bold;
                                                            "
                                                            class="shadow-sm">
                                
                                                                PDF
                                
                                                            </div>
                                
                                                        </a>
                                
                                                    @endif
                                
                                                @endforeach
                                
                                            </div>
                                
                                        @else
                                
                                            <div class="text-muted small">
                                                No Bill uploaded
                                            </div>
                                
                                        @endif
                                
                                    @endif
                                
                                
                                    <!-- NEW FILE PREVIEW -->
                                    @if(!empty($physical_order_bill_book_new))
                                
                                        <div class="d-flex flex-wrap gap-2">
                                
                                            @foreach($physical_order_bill_book_new as $file)
                                
                                                @php
                                                    $extension = strtolower($file->getClientOriginalExtension());
                                                @endphp
                                
                                                <!-- IMAGE -->
                                                @if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                                
                                                    <img src="{{ $file->temporaryUrl() }}"
                                                         style="width:60px;height:60px;object-fit:cover;"
                                                         class="img-thumbnail shadow-sm" >
                                
                                                <!-- PDF -->
                                                @elseif($extension == 'pdf')
                                
                                                    <a href="{{ $file->temporaryUrl() }}"
                                                       target="_blank"
                                                       class="text-decoration-none" >
                                
                                                        <div style="
                                                            width:60px;
                                                            height:60px;
                                                            border:1px solid #ccc;
                                                            display:flex;
                                                            align-items:center;
                                                            justify-content:center;
                                                            background:#f8f9fa;
                                                            font-size:12px;
                                                            font-weight:bold;
                                                        "
                                                        class="shadow-sm">
                                
                                                            PDF
                                
                                                        </div>
                                
                                                    </a>
                                
                                                @endif
                                
                                            @endforeach
                                
                                        </div>
                                
                                    @endif
                                
                                </div>
                            
                                <!-- Verified Video -->
                                <div class="col-md-3">
                                    <p class="small mb-1 text-bold">Verified Video</p>
                            
                                   @if(!empty($verified_video))

                                        @foreach($verified_video as $video)
                                    
                                            <video width="120" controls class="shadow-sm">
                                                <source src="{{ asset($video) }}">
                                            </video>
                                    
                                        @endforeach
                                    
                                    @else
                                    <div class="text-muted small">No video uploaded</div>
                                    @endif
                                </div>
                                
                                 <!-- Verified Audio -->
                               <div class="col-md-3">
                                    <p class="small mb-1 text-bold">Verified Audio</p>
                                
                                    @if(!empty($verified_audio))

                                        @foreach($verified_audio as $audio)
                                    
                                            <audio controls style="width:80px;">
                                                <source src="{{ asset($audio) }}">
                                            </audio>
                                    
                                        @endforeach
                                    
                                    @else
                                    <div class="text-muted small">No audio uploaded</div>
                                    @endif
                                </div>
                            </div>
                            
                        {{-- Customer Image only for garment end --}}
                        <div class="row">
                            <div class="col-12 col-md-12 mb-2">
                                <h6 class="badge bg-danger custom_danger_badge">Product Information</h6>
                            </div>
                        </div>
                         @if ($errors->has('items'))
                        <div class="alert alert-danger">
                            {{ $errors->first('items') }}
                        </div>
                        @endif
                        <!-- Loop through items -->
                        @foreach($items as $index => $item)
                            @php
                                $status = $item['status'] ?? null;
                                $tlStatus = $item['tl_status'] ?? null;
                                $adminStatus = $item['admin_status'] ?? null;
                                $auth = Auth::guard('admin')->user();
                                $priority_level = in_array($auth->designation,[1,4]);
                                // Disable rules
                                if ($adminStatus === 'Approved') {
                                // If Admin approved â†’ nobody can edit
                                $isDisabled = true;
                                } elseif ($status === 'Process' && $tlStatus === 'Approved') {
                                // If TL approved but Admin not yet â†’ disable for Sales & TL
                                $isDisabled = in_array($auth->designation, [2,4]);
                                } else {
                                // Otherwise keep editable
                                $isDisabled = false;
                                }
                            @endphp
                        <div class="row align-items-center my-3">
                            <div class="col-auto">
                                <span class="text-sm badge bg-primary sale_grn_sl">{{$index + 1}}</span>
                            </div>
                            <!-- Collection -->
                            <div class="mb-2 col-md-2">
                                <label class="form-label"><strong>Collection </strong><span
                                        class="text-danger">*</span></label>
                                <select wire:model="items.{{ $index }}.selected_collection"
                                    wire:change="GetCategory($event.target.value, {{ $index }})"
                                    class="form-control border border-2 p-2 form-control-sm" @if($isDisabled) disabled
                                    @endif>
                                    <option value="" selected hidden>Select collection</option>
                                    @foreach($collections as $citems)
                                    <option value="{{ $citems->id }}" {{$item['selected_collection']==$citems->id ?
                                        "selected" : ""}}>{{ strtoupper($citems->title) }}
                                        @if($citems->short_code)
                                        ({{ $citems->short_code }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error("items.".$index.".selected_collection")
                                <p class='text-danger inputerror'>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="mb-2 col-md-2">
                                <label class="form-label"><strong>Category</strong></label>
                                <select wire:model="items.{{ $index }}.selected_category"
                                    class="form-select form-control-sm border border-1"
                                    wire:change="CategoryWiseProduct($event.target.value, {{ $index }})"
                                    @if($isDisabled) disabled @endif>
                                    <option value="" selected hidden>Select Category</option>
                                    @foreach ($item['categories'] as $category)
                                    <option value="{{ $category['id'] }}" {{$item['selected_category'] == $category['id'] ? "selected" : "" }}>
                                        {{ strtoupper($category['title']) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error("items.".$index.".selected_category")
                                <p class="text-danger inputerror">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Product -->
                            @if(isset($item['selected_collection']) && $item['selected_collection'] == 1)
                            <div class="mb-2 col-md-2">
                                @else

                                <div class="mb-2 col-md-3">
                                    @endif
                                    <label class="form-label"><strong>Product</strong></label>
                                    <input type="text" wire:keyup="FindProduct($event.target.value, {{ $index }})"
                                        wire:model="items.{{ $index }}.searchproduct"
                                        class="form-control form-control-sm border border-1 customer_input"
                                        placeholder="Enter product name" value="{{ $item['searchproduct'] }}"
                                        @if($isDisabled) disabled @endif>

                                    @error("items.".$index.".searchproduct")
                                    <p class="text-danger inputerror">{{ $message }}</p>
                                    @enderror

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
                                </div>
                                @if(isset($item['selected_collection']) && $item['selected_collection'] != 1)
                                {{-- Quantity field shown for all except collection 1 --}}
                                <div class="col-md-2 col-12 mb-3">
                                    <label class="form-label"><strong>Quantity</strong><span
                                            class="text-danger">*</span></label>
                                    <input type="number" wire:model="items.{{ $index }}.quantity" class="form-control form-control-sm border border-1 customer_input
                                            @error('items.' . $index . '.quantity') border-danger @enderror"
                                        placeholder="Enter quantity" min="1" @if($isDisabled) disabled @endif>
                                    @error('items.' . $index . '.quantity')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                @else
                                {{-- Hidden quantity field for collection 1 to preserve value --}}
                                <input type="hidden" wire:model="items.{{ $index }}.quantity">
                                @endif

                                @if(isset($item['selected_collection']) && $item['selected_collection'] == 1)
                                <!-- Fabrics -->
                                <div class="mb-2 col-12 col-md-2">
                                    <label class="form-label"><strong>Fabric</strong></label>
                                    <input type="text" wire:model="items.{{ $index }}.searchTerm"
                                        wire:keyup="searchFabrics({{ $index }})" class="form-control form-control-sm"
                                        placeholder="Search by fabric name" id="searchFabric_{{ $index }}"
                                        value="{{ optional(collect($items[$index]['fabrics'])->firstWhere('id', $items[$index]['selected_fabric']))->title }}"
                                        autocomplete="off" @if($isDisabled) disabled @endif>
                                    @error("items.{$index}.searchTerm")
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                    @if(!empty($items[$index]['searchResults']))
                                    <div class="dropdown-menu show w-100"
                                        style="max-height: 187px; max-width: 100px; overflow-y: auto;">
                                        @foreach ($items[$index]['searchResults'] as $fabric)
                                        <button class="dropdown-item fabric_dropdown_item" type="button"
                                            wire:click="selectFabric({{ $fabric->id }}, {{ $index }})">
                                            {{ $fabric->title }}({{$fabric->available_stock}} m)
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                {{-- Price --}}
                                <div class="mb-2 col-12 col-md-2">
                                    <div class="d-flex align-items-end gap-2">
                                        <!-- Price Input -->
                                        <div>
                                            <label class="form-label"><strong>Price</strong></label>
                                            <input type="text"
                                                wire:keyup="checkproductPrice($event.target.value, {{ $index }})"
                                                wire:model="items.{{ $index }}.price" class="form-control form-control-sm border border-1 customer_input
                                            @if(session()->has('errorPrice.' . $index)) border-danger @endif
                                            @error('items.' . $index . '.price') border-danger  @enderror"
                                                placeholder="Enter Price" @if($isDisabled) disabled @endif>
                                        </div>
                                        <div>
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm danger_btn mb-0"
                                                wire:click="removeItem({{ $index }})" @if($isDisabled) disabled @endif>
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Error Messages -->
                                    @if(session()->has('errorPrice.' . $index))
                                    <div class="text-danger">{{ session('errorPrice.' . $index) }}</div>
                                    @endif

                                    @error('items.' . $index . '.price')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror


                                </div>
                                @else
                                <div class="col-12 col-md-2 mb-2">
                                    <div class="d-flex align-items-end gap-2 justify-content-end">
                                        <div>
                                            <!-- Price Input -->
                                            <label class="form-label"><strong>Price</strong></label>
                                            <input type="text"
                                                wire:keyup="checkproductPrice($event.target.value, {{ $index }})"
                                                wire:model="items.{{ $index }}.price"
                                                class="form-control form-control-sm border border-1 customer_input @if(session()->has('errorPrice.' . $index)) border-danger @endif @error('items.' . $index . '.price') border-danger @enderror"
                                                placeholder="Enter Price" @if($isDisabled) disabled @endif>
                                        </div>
                                        <div>
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm danger_btn mb-0"
                                                wire:click="removeItem({{ $index }})" @if($isDisabled) disabled
                                                @endif><span class="material-icons">delete</span>
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
                                @if(isset($item['selected_collection']) && $items[$index]['selected_collection'] == 2)
                              
                                <div class="row mb-3">

                                    {{-- Delivery Date --}}
                                    <div class="col-md-2">
                                        <label for="">Delivery Date</label>
                                
                                        <input type="month"
                                            class="form-control form-control-sm border border-1"
                                            wire:model="items.{{$index}}.expected_delivery_date"
                                            min="{{ \Carbon\Carbon::today()->format('Y-m') }}"
                                            @if($isDisabled) disabled @endif>
                                
                                        @error("items.$index.expected_delivery_date")
                                            <div class="text-danger error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                
                                    {{-- Admin / TL Card Design --}}
                                    @if($priority_level)
                                
                                        <div class="col-md-5">
                                            <div class="card border shadow-sm">
                                
                                                <div class="card-header py-2 bg-light">
                                                    <strong>Management Controls</strong>
                                                </div>
                                
                                                <div class="card-body py-3">
                                                    <div class="row">
                                
                                                        {{-- Priority --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">
                                                                <strong>Priority Level</strong>
                                                            </label>
                                
                                                            <select class="form-control form-control-sm border border-1"
                                                                wire:model="items.{{ $index }}.priority"
                                                                @if($isDisabled) disabled @endif>
                                
                                                                <option value="" hidden>Select Priority</option>
                                                                <option value="Priority">Priority</option>
                                                                <option value="Non Priority">Non Priority</option>
                                                            </select>
                                
                                                            @error("items.$index.priority")
                                                                <div class="text-danger error-message">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                
                                                        {{-- Status --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">
                                                                <strong>Item Status</strong>
                                                            </label>
                                
                                                            <select class="form-control form-control-sm border border-1"
                                                                wire:model="items.{{ $index }}.item_status"
                                                                @if($isDisabled) disabled @endif>
                                
                                                                <option value="" hidden>Select Item Status</option>
                                                                <option value="Process">Process</option>
                                                                <option value="Hold">Hold</option>
                                                            </select>
                                
                                                            @error("items.$index.item_status")
                                                                <div class="text-danger">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                
                                                    </div>
                                                </div>
                                
                                            </div>
                                        </div>
                                
                                    @else
                                
                                        {{-- Normal Design For Other Users --}}
                                        <div class="col-md-2">
                                            <label class="form-label">
                                                <strong>Priority Level</strong>
                                            </label>
                                
                                            <select class="form-control form-control-sm border border-1"
                                                wire:model="items.{{ $index }}.priority"
                                                @if($isDisabled) disabled @endif>
                                
                                                <option value="" hidden>Select Priority</option>
                                                <option value="Priority">Priority</option>
                                                <option value="Non Priority">Non Priority</option>
                                            </select>
                                
                                            @error("items.$index.priority")
                                                <div class="text-danger error-message">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                
                                        <div class="col-md-2">
                                            <label class="form-label">
                                                <strong>Item Status</strong>
                                            </label>
                                
                                            <select class="form-control form-control-sm border border-1"
                                                wire:model="items.{{ $index }}.item_status"
                                                @if($isDisabled) disabled @endif>
                                
                                                <option value="" hidden>Select Item Status</option>
                                                <option value="Process">Process</option>
                                                <option value="Hold">Hold</option>
                                            </select>
                                
                                            @error("items.$index.item_status")
                                                <div class="text-danger">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                
                                    @endif
                                
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label"><strong>Remarks</strong></label>
                                        
                                        <textarea type="text" wire:model="items.{{ $index }}.remarks"
                                            class="form-control form-control-sm border border-1 customer_input" 
                                            oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'"
            
                                            style="resize:none; overflow:hidden; field-sizing: content; min-height: 80px;"
                                            
                                            x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                            placeholder="Enter Product Remarks"
                                            @if($isDisabled) disabled @endif >
                                        </textarea>
                                        @error("items.".$index.".remarks")
                                           <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endif
                                @endif
                            </div>
                            
                            @if(isset($this->items[$index]['product_id']) && $items[$index]['selected_collection'] == 1)
                            <div class="row">
                                {{-- ============================================================ --}}
                                {{-- BLOCK 1: Delivery Date · Fittings · Priority Level · Status --}}
                                {{-- ============================================================ --}}

                                   <div class="col-12 mt-3">
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label for="">Delivery Date</label>
                                                <input type="month" class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{$index}}.expected_delivery_date"
                                                    min="{{ \Carbon\Carbon::today()->format('Y-m') }}"
                                                    @if($isDisabled) disabled @endif>
                                                @error("items.$index.expected_delivery_date")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            {{-- Fittings --}}
                                            <div class="col-md-3">
                                                <label class="form-label"><strong>Fittings</strong></label>
                                                <select class="form-control form-control-sm border border-1"
                                                    wire:model="items.{{ $index }}.fitting" @if($isDisabled) disabled
                                                    @endif>
                                                    <option value="" hidden>Select Fitting</option>
                                                    <option value="Regular Fit">Regular Fit</option>
                                                    <option value="Slim Fit">Slim Fit</option>
                                                    <option value="Loose Fit">Loose Fit</option>
                                                </select>
                                                @error("items.{$index}.fitting")
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            {{-- Admin / TL Card Design --}}
                                            @if($priority_level)
                                        
                                                <div class="col-md-6">
                                                    <div class="card border shadow-sm h-100">
                                        
                                                        <div class="card-header bg-light py-2">
                                                            <strong>Management Controls</strong>
                                                        </div>
                                        
                                                        <div class="card-body py-3">
                                                            <div class="row">
                                        
                                                                {{-- Priority Level --}}
                                                                <div class="col-md-6">
                                                                    <label class="form-label">
                                                                        <strong>Priority Level</strong>
                                                                    </label>
                                        
                                                                    <select class="form-control form-control-sm border border-1"
                                                                        wire:model="items.{{ $index }}.priority"
                                                                        @if($isDisabled) disabled @endif>
                                        
                                                                        <option value="" hidden>Select Priority</option>
                                                                        <option value="Priority">Priority</option>
                                                                        <option value="Non Priority">Non Priority</option>
                                                                    </select>
                                        
                                                                    @error("items.$index.priority")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                        
                                                                {{-- Item Status --}}
                                                                <div class="col-md-6">
                                                                    <label class="form-label">
                                                                        <strong>Item Status</strong>
                                                                    </label>
                                        
                                                                    <select class="form-control form-control-sm border border-1"
                                                                        wire:model="items.{{ $index }}.item_status"
                                                                        @if($isDisabled) disabled @endif>
                                        
                                                                        <option value="" hidden>Select Item Status</option>
                                                                        <option value="Process">Process</option>
                                                                        <option value="Hold">Hold</option>
                                                                    </select>
                                        
                                                                    @error("items.$index.item_status")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                        
                                                            </div>
                                                        </div>
                                        
                                                    </div>
                                                </div>
                                        
                                            @else
                                        
                                                {{-- Normal Layout For Other Users --}}
                                                
                                        
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        <strong>Item Status</strong>
                                                    </label>
                                        
                                                    <select class="form-control form-control-sm border border-1"
                                                        wire:model="items.{{ $index }}.item_status"
                                                        @if($isDisabled) disabled @endif>
                                        
                                                        <option value="" hidden>Select Item Status</option>
                                                        <option value="Process">Process</option>
                                                        <option value="Hold">Hold</option>
                                                    </select>
                                        
                                                    @error("items.$index.item_status")
                                                        <div class="text-danger error-message">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                        
                                            @endif
                                        </div>
                                    </div>
                                {{-- ============================================================ --}}
                                {{-- END BLOCK 1                                                  --}}
                                {{-- ============================================================ --}}
                                
                                {{-- ============================================================ --}}
                                {{-- BLOCK 2: Catalogue · Page Number · Page Item                --}}
                                {{-- ============================================================ --}}
                                 <div class="col-12 mt-3">
                                    <div class="row g-3 mb-3">
                                        {{-- Catalogue --}}
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"><strong>Catalogue</strong></label>
                                            <select wire:model="items.{{ $index }}.selectedCatalogue"
                                                class="form-control form-control-sm border border-1 @error('items.'.$index.'.selectedCatalogue') border-danger @enderror"
                                                wire:change="SelectedCatalogue($event.target.value, {{ $index }})"
                                                @if($isDisabled) disabled @endif>
                                                <option value="" selected hidden>Select Catalogue</option>
                                                @foreach($item['catalogues'] ?? [] as $cat_log)
                                                <option value="{{ $cat_log['id'] }}">
                                                    {{ $cat_log['catalogue_title']['title'] ?? 'No Title' }}
                                                    @if(($cat_log['catalogue_title']['title'] ?? '') !== 'No Catalogue
                                                    Images')
                                                    (1 - {{ $cat_log['page_number'] }})
                                                    @endif

                                                </option>
                                                @endforeach
                                            </select>
                                           @error("items.{$index}.selectedCatalogue")
                                                <div class="text-danger">{{ $message }}</div>
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
                                                max="{{ isset($item['selectedCatalogue']) && isset($maxPages[$index][$item['selectedCatalogue']]) ? $maxPages[$index][$item['selectedCatalogue']] : '' }}"
                                                @if($isDisabled) disabled @endif 
                                            @if(
                                            $orders !== null
                                            &&
                                            $orders->status !== 'On Hold'
                                            && isset($item['selectedCatalogue'])
                                            && isset($item['catalogues'])
                                            && ($catalogue = collect($item['catalogues'])->firstWhere('id',
                                            $item['selectedCatalogue']))
                                            && isset($catalogue['catalogue_title'])
                                            && isset($catalogue['catalogue_title']['title'])
                                            && $catalogue['catalogue_title']['title'] === 'No Catalogue Images'
                                            )
                                            disabled
                                            @endif

                                            >
                                            @error("items.{$index}.page_number")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        {{-- Page item --}}
                                        <div class="mb-3 col-md-5">
                                            {{-- @if(isset($catalogue_page_item) &&
                                            !empty($catalogue_page_item[$index])) --}}
                                            @if(!empty($items[$index]['pageItems']))
                                            <label class="form-label"><strong>Page Item</strong></label>
                                            <select wire:model="items.{{$index}}.page_item"
                                                class="form-control form-control-sm border border-2 @error('items.'.$index.'.page_item') border-danger @enderror"
                                                @if($isDisabled) disabled @endif @if( $orders->status !== 'On Hold'
                                                && isset($item['selectedCatalogue'])
                                                && isset($item['catalogues'])
                                                && ($catalogue = collect($item['catalogues'])->firstWhere('id',
                                                $item['selectedCatalogue']))
                                                && isset($catalogue['catalogue_title'])
                                                && isset($catalogue['catalogue_title']['title'])
                                                && $catalogue['catalogue_title']['title'] === 'No Catalogue Images'
                                                )
                                                disabled
                                                @endif

                                                >
                                                <option value="" selected hidden>Select Page Item</option>
                                                {{-- @if(!empty($items[$index]['pageItems'])) --}}
                                                @foreach($items[$index]['pageItems'] as $pageItems)
                                                <option value="{{ $pageItems }}"
                                                    {{$items[$index]['pageItems']==$pageItems ? 'selected' : '' }}>
                                                    {{ $pageItems }}
                                                </option>
                                                @endforeach
                                                {{-- @endif --}}
                                            </select>
                                            @error("items.$index.page_item")
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- ============================================================ --}}
                                {{-- END BLOCK 2                                                  --}}
                                {{-- ============================================================ --}}
                                        
                                      
                                        
                                {{-- ============================================================ --}}
                                {{-- BLOCK 3: Extra Measurements + Upload Section                --}}
                                {{-- ============================================================ --}}        
                                        @php
                                            $extras = $extra_measurement[$index] ?? [];
                                        @endphp
                                        @if(count($extras)>0)
                                        <div class="col-12 my-3">
                                            <div class="row mb-4">
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
                                                                                wire:change="validateSingle('items.{{ $index }}.vents')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:change="validateSingle('items.{{ $index }}.client_name_required')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:model="items.{{ $index }}.client_name_place" oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'" 
                                                                            style="resize:none;"
                                                                                wire:keydown.enter.prevent @if($isDisabled) disabled @endif>
                                                                            
                                                                            @error("items.$index.client_name_place")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        @endif
                                                                        {{-- Shoulder Type --}}
                                                                        <div class="col-md-3">
                                                                            <label><strong>Shoulder Type</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:model="items.{{ $index }}.shoulder_type"
                                                                                wire:change="validateSingle('items.{{ $index }}.shoulder_type')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:model="items.{{ $index }}.mens_hand_stitching"
                                                                                wire:change="validateSingle('items.{{ $index }}.mens_hand_stitching')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:change="validateSingle('items.{{ $index }}.shoulder_type')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:change="validateSingle('items.{{ $index }}.vents_required')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:change="validateSingle('items.{{ $index }}.vents_count')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:change="validateSingle('items.{{ $index }}.client_name_required')"
                                                                                @if($isDisabled) disabled @endif>
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
                                                                                wire:keydown.enter.prevent @if($isDisabled) disabled @endif>
                                                                           
                                                                            @error("items.$index.client_name_place")
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        @endif
                                                                        
                                                                        {{--Hand Stitching--}}
                                                                        <div class="col-md-3">
                                                                            <label><strong>Hand Stitching</strong></label>
                                                                            <select class="form-control form-control-sm"
                                                                                wire:model="items.{{ $index }}.ladies_hand_stitching"
                                                                                wire:change="validateSingle('items.{{ $index }}.ladies_hand_stitching')">
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
                                                    @if(in_array('trouser',$extras))
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
                                                                            wire:model="items.{{ $index }}.fold_cuff_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.fold_cuff_required')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            @if($isDisabled) disabled @endif>
                                                                        @error("items.$index.fold_cuff_size")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        
                                                                    </div>
                                                                    @endif
                        
                                                                    <!-- Pleats -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Pleats</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.pleats_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.pleats_required')"
                                                                            @if($isDisabled) disabled @endif>
                                                                            <!--<option value="" hidden>Select Option</option>-->
                                                                             <option value="No">No</option>
                                                                            <option value="1">1</option>
                                                                            <option value="1(Centre Crease)">1(Centre Crease)</option>
                                                                            <option value="2">2</option>
                                                                        </select>
                                                                        @error("items.$index.pleats_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                      
                                                                    </div>
                        
                                                                  {{--  @if(!empty($items[$index]['pleats_required']) &&
                                                                    $items[$index]['pleats_required'] == 'Yes')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>How Many Pleats?</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.pleats_count"
                                                                            wire:change="validateSingle('items.{{ $index }}.pleats_count')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            wire:model="items.{{ $index }}.back_pocket_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.back_pocket_required')"
                                                                            @if($isDisabled) disabled @endif>
                                                                             <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.back_pocket_required")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        
                                                                    </div>
                        
                                                                  {{--  @if(!empty($items[$index]['back_pocket_required']) &&
                                                                    $items[$index]['back_pocket_required'] == 'Yes')
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>How Many Pockets?</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.back_pocket_count"
                                                                            wire:change="validateSingle('items.{{ $index }}.back_pocket_count')"
                                                                            @if($isDisabled) disabled @endif>
                                                                            <option value="" hidden>Select Count</option>
                                                                            <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                            <option value="3">3</option>
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
                                                                            wire:change="validateSingle('items.{{ $index }}.adjustable_belt')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            wire:change="validateSingle('items.{{ $index }}.suspender_button')"
                                                                            @if($isDisabled) disabled @endif>
                                                                            <option value="" hidden>Select Option</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                        @error("items.$index.suspender_button")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        {{-- <span class="tooltip-text">Specify if suspender buttons are
                                                                            needed.</span> --}}
                                                                    </div>
                        
                                                                    <!-- Trouser Position -->
                                                                    <div class="col-md-3 tooltip-wrapper">
                                                                        <label class="form-label"><strong>Trouser Position</strong></label>
                                                                        <select class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.trouser_position"
                                                                            wire:change="validateSingle('items.{{ $index }}.trouser_position')"
                                                                            @if($isDisabled) disabled @endif>
                                                                            <option value="" hidden>Select Position</option>
                                                                            <option value="High Waist">High Waist</option>
                                                                            <option value="Normal">Normal</option>
                                                                            <option value="Low Waist">Low Waist</option>
                                                                        </select>
                                                                        @error("items.$index.trouser_position")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                        {{-- <span class="tooltip-text">Select waist position for
                                                                            trousers.</span>
                                                                        --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
        
                                                  {{-- ================= SHIRT DETAILING SECTION ================= --}}
                                                    @if(in_array('shirt',$extras))
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
                                                                            wire:change="validateSingle('items.{{ $index }}.sleeves')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            wire:change="validateSingle('items.{{ $index }}.collar')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                        <label class="form-label"><strong>Collar Styles</strong></label>
                                                                        <textarea type="text" class="form-control form-control-sm border border-1"
                                                                            wire:model="items.{{ $index }}.collar_style"   
                                                                             oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'"
                                                                             style="resize:none;"
                                                                            wire:change="validateSingle('items.{{ $index }}.collar_style')"
                                                                            placeholder="Enter Collar Style" @if($isDisabled) disabled @endif></textarea>
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
                                                                            wire:change="validateSingle('items.{{ $index }}.pocket')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            wire:change="validateSingle('items.{{ $index }}.cuffs')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            placeholder="Enter Cuff Style" @if($isDisabled) disabled @endif>
                                                                        @error("items.$index.cuff_style")
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    @endif
                                                                    <div class="col-md-3">
                                                                        <label><strong>Client Name</strong></label>
                                                                        <select class="form-control form-control-sm"
                                                                            wire:model="items.{{ $index }}.client_name_required"
                                                                            wire:change="validateSingle('items.{{ $index }}.client_name_required')"
                                                                            @if($isDisabled) disabled @endif>
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
                                                                            wire:keydown.enter.prevent @if($isDisabled) disabled @endif>
                                                                       
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

                                    <div class="mb-3 col-12">
                                        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-start">
                                            <div class="d-flex flex-column gap-2 flex-grow-1">
                                                {{-- Image Preview --}}
                                                @if (!empty($existingImages[$index]))
                                                <div class="d-flex flex-row flex-wrap gap-2">
                                                    @foreach ($existingImages[$index] as $image)
                                                    <div style="position: relative; width: 70px;">
                                                        <img src="{{ asset('storage/' . $image) }}"
                                                            class="img-thumbnail" style="width: 100%;" />
                                                        <button type="button" @if($isDisabled) disabled @endif
                                                            class="btn btn-sm rounded-circle p-1 btn-danger position-absolute top-0 end-0"
                                                            style="width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center;"
                                                            wire:click="removeImage({{ $index }}, '{{ $loop->index }}')">
                                                            &times;
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                                {{-- Show newly uploaded temporary images --}}
                                                @if (!empty($imageUploads[$index]))
                                                <div class="d-flex flex-row flex-wrap gap-2">
                                                    @foreach ($imageUploads[$index] as $img)
                                                    <div style="position: relative; width: 70px">
                                                        <img src="{{ $img->temporaryUrl() }}" class="img-thumbnail"
                                                            style="width: 100%; height: 100%; object-fit: cover;" />
                                                        <button type="button" @if($isDisabled) disabled @endif
                                                            class="btn btn-sm rounded-circle p-1 btn-danger position-absolute top-0 end-0"
                                                            style="width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center;"
                                                            wire:click="removeUploadedImage({{ $index }}, {{$loop->index }})">
                                                            &times;
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif

                                                @if (!empty($existingVideos[$index]))
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($existingVideos[$index] as $video)

                                                    <div style="position: relative; width: 150px;">
                                                        <audio controls style="width: 100%;" @if($isDisabled) disabled
                                                            @endif>
                                                            <source src="{{ asset('storage/' . $video) }}"
                                                                type="audio/mpeg">
                                                            Your browser does not support the audio element.
                                                        </audio>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger rounded-circle p-1 position-absolute top-0 end-0"
                                                            style="width: 24px; height: 24px; font-size: 14px; display: flex; align-items: center; justify-content: center;"
                                                            wire:click="removeVideo({{ $index }}, '{{ $loop->index }}')"
                                                            @if($isDisabled) disabled @endif>
                                                            &times;
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif

                                                {{-- Newly Uploaded Voice Preview --}}
                                                @if (!empty($voiceUploads[$index]))
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    @foreach ($voiceUploads[$index] as $voiceIndex => $voice)
                                                    <div style="position: relative; width: 150px;">
                                                        <audio controls style="width: 100%;" @if($isDisabled) disabled
                                                            @endif>
                                                            <source src="{{ $voice->temporaryUrl() }}"
                                                                type="audio/mpeg">
                                                            Your browser does not support the audio element.
                                                        </audio>
                                                        <button type="button"
                                                            class="btn btn-sm rounded-circle p-1 btn-danger position-absolute top-0 end-0"
                                                            style="width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center;"
                                                            wire:click="removeUploadedVoice({{ $index }}, {{ $voiceIndex }})"
                                                            @if($isDisabled) disabled @endif>
                                                            &times;
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-2">
                                                {{-- Upload Image --}}

                                                <button type="button" class="btn btn-cta btn-sm"
                                                    onclick="document.getElementById('catalog-upload-{{ $index }}').click()"
                                                    @if($isDisabled) disabled @endif>
                                                    <i class="material-icons text-white"
                                                        style="font-size: 15px;">add</i>
                                                    Upload Images
                                                </button>
                                                {{-- Upload Voice --}}
                                                <div class="d-flex align-items-center gap-3">
                                                    <!-- Upload Voice Button -->
                                                    <button type="button" class="btn btn-cta btn-sm"
                                                        onclick="document.getElementById('voice-upload-{{ $index }}').click()"
                                                        @if($isDisabled) disabled @endif>
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
                                                            id="startBtn_{{ $index }}" @if($isDisabled) disabled @endif>
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

                                                @error('imageUploads.*')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                @error('voiceUploads.*')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Hidden File Input --}}
                                            <input type="file" id="catalog-upload-{{ $index }}" multiple
                                                wire:model="newUploads.{{ $index }}" accept="image/*" class="d-none"
                                                @if($isDisabled) disabled @endif />
                                            {{-- Voice Upload --}}
                                            <input type="file" id="voice-upload-{{ $index }}" multiple
                                                wire:model="voiceUploads.{{ $index }}" accept="audio/*" class="d-none"
                                                @if($isDisabled) disabled @endif />
                                        </div>
                                    </div>
                                {{-- ============================================================ --}}
                                {{-- END BLOCK 3                                                  --}}
                                {{-- ============================================================ --}}   
                                
                                
                                {{-- ============================================================ --}}
                                {{-- BLOCK 4: Remarks                                            --}}
                                {{-- ============================================================ --}}
                                  <div class="row mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label"><strong>Remarks</strong></label>
                                               
                                                <textarea type="text" wire:model="items.{{ $index }}.remarks" 
                                                    class="form-control form-control-sm border border-1 customer_input"
                                                    oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'"
            
                                                    style="resize:none; overflow:hidden; field-sizing: content; min-height: 80px;"
                                                    
                                                    x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                                    placeholder="Enter Product Remarks" @if($isDisabled) disabled
                                                    @endif></textarea>
                                                    
                                                @error("items.".$index.".remarks")
                                                <div class="text-danger error-message">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                {{-- ============================================================ --}}
                                {{-- END BLOCK 4                                                  --}}
                                {{-- ============================================================ --}}
                                </div>

                           
                           
                            @endif
                            @endforeach
                            
                           
                            
                            <!-- Add Item Button and Payment Section -->
                            <div class="row align-items-end mb-4" style="justify-content: end;">
                                <div class="col-md-4" style="text-align: -webkit-center;">
                                    <table>
                                        <tr>
                                            <td>
                                                @if (session()->has('errorAmount'))
                                                <div class="alert alert-danger">
                                                    {{ session('errorAmount') }}
                                                </div>
                                                @endif
                                            </td>
                                            <td style="text-align: end;">
                                                <button type="button" class="btn btn-success btn-sm" wire:click="addItem"><i
                                                        class="material-icons text-white"
                                                        style="font-size: 15px;">add</i>Add Item</button>
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td class="w-70"><label class="form-label"><strong>Air Mail</strong></label>
                                            </td>
                                            <td>
                                                <!-- Sub Total -->
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="air_mail" wire:keyup="updateBillingAmount"
                                                    placeholder="Enter air mail cost">
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td class="w-70"><label class="form-label"><strong>Total
                                                        Amount</strong></label>
                                            </td>
                                            <td>
                                                <!-- Sub Total -->
                                                <input type="text" class="form-control form-control-sm"
                                                    wire:model="billing_amount" disabled
                                                    value="{{ number_format($billing_amount, 2) }}">
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                            </div>
                            
                             {{-- Unified Measurement Panel Dashboard Setup Component Inject Block --}}
                             <div class="card my-4 shadow-sm border border-secondary" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header bg-dark d-flex justify-content-between align-items-center py-3">
                            <h6 class="mb-0 text-primary font-weight-bold d-flex align-items-center">
                                <i class="material-icons me-2 text-warning" style="font-size: 22px;">straighten</i> 
                                Measurement Entry Layout Control Panel
                            </h6>
                            <button type="button" class="btn btn-sm {{ $isUnifiedViewActive ? 'btn-warning' : 'btn-outline-primary' }} mb-0 px-3 shadow-none text-uppercase" 
                                    wire:click="toggleUnifiedMeasurementView">
                                {{ $isUnifiedViewActive ? ' Hide Measurement' : 'Show Measurement' }}
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
                                            <div class="col" wire:key="unified-field-matrix-edit-{{ $loopKey }}">
                                                <div class="border rounded p-2 bg-light shadow-2xs h-100 d-flex flex-column justify-content-between" style="border-color: #dee2e6; border-radius: 6px;">
                                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                                        <label class="mb-0 font-weight-bold text-dark small text-uppercase" style="max-width: 85%; line-height: 1.2;">
                                                            {{ $uField['title'] }} 
                                                            <span class="text-danger font-weight-bolder">[{{ $uField['short_code'] }}]</span>
                                                        </label>
                                                        <span class="message-icon" style="cursor:pointer; font-size:14px; opacity: 0.7;" wire:click="$toggle('unifiedMeasurements.{{ $loopKey }}.show_remarks')">
                                                            <i class="fas fa-comment-alt"></i>
                                                        </span >
                                                    </div>
                                                    
                                                    <input type="text" 
                                                           wire:model.live="unifiedMeasurements.{{ $loopKey }}.value" required
                                                           class="form-control form-control-sm text-center border text-dark font-weight-bold bg-white"
                                                           style="border-color: #ced4da; border-radius: 4px; font-size: 0.95rem; height: 36px;">
                                                            
                                                    @error("unifiedMeasurements.{$loopKey}.value")
                                                        <span class="text-danger small mt-1" style="font-size: 0.7rem;">{{ $message }}</span>
                                                    @enderror
                                                            
                                                    @if(!empty($uField['show_remarks']))
                                                        <div class="mt-2">
                                                            <textarea class="form-control form-control-sm border p-2" 
                                                                      rows="2" 
                                                                      placeholder="Adjustment specification remarks..."
                                                                      wire:model.live="unifiedMeasurements.{{ $loopKey }}.remarks"
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
                           {{-- Unified Measurement Panel Dashboard Setup Component Inject Block End--}}
                           
                        </div>
                        <div class="d-flex justify-content-end align-items-center mb-3">
                            @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                            @endif
                            @if($activeTab>1)
                            <button type="button" class="btn btn-dark mx-2 btn-sm"
                                wire:click="TabChange({{$activeTab-1}})"><i
                                    class="material-icons text-white">chevron_left</i>Previous</button>
                            <button type="submit" class="btn btn-primary mx-2 btn-sm"><i
                                    class="material-icons text-white">add</i>Update Order</button>
                            @endif
                            @if($activeTab==1)
                            <button type="button" class="btn btn-cta mx-2 btn-sm"
                                wire:click="TabChange({{$activeTab+1}})">Next<i
                                    class="material-icons text-white">chevron_right</i></button>
                            @endif

                        </div>
            </form>

        </div>
    </div>
    <div class="loader-container" wire:loading wire:target="update">
        <div class="loader"></div>
        <h5>Updating Order, please wait...</h5>
    </div>
</div>

@push('js')

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

        // console.log(`File assigned to input #voice-upload-${index}`);
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

    function initIntlTelInput(selector, phoneModel, codeModel) {
        var input = $(selector);
        var codeInput = $("#" + codeModel);
        var phoneInput = $("#" + phoneModel);
        
        var selected_dial_code = codeInput.val(); // Get the stored dial code
        var selected_phone_number = phoneInput.val(); // Get the stored phone number
        
        var defaultCountry = loadDialCodes(selected_dial_code);
        
        // Initialize intlTelInput
        input.intlTelInput({
            initialCountry: defaultCountry,
            preferredCountries: ["us", "gb", "in", "cf"],
            separateDialCode: true
        });
        
        // Set the phone number value
        input.val(selected_phone_number);
        
        //  KEY FIX: Manually trigger mobile length update for pre-selected country
        setTimeout(function() {
            let countryData = input.intlTelInput("getSelectedCountryData");
            let code = "+" + countryData.dialCode;
            
            // Set the code in Livewire
            @this.set(codeModel, code);
            
            // Call CountryCodeSet to set mobile length
            @this.call('CountryCodeSet', selector, code);
            
            console.log(`Initial mobile length set for ${selector} with code ${code}`);
        }, 100); // Small delay to ensure intlTelInput is fully initialized
        
        // On input change (number typing)
        input.on("input keyup", function () {
            let number = input.val().replace(/\D/g, ''); // only digits
            @this.set(phoneModel, number);
        });

        // On country change
        input.on("countrychange", function () {
            let countryData = input.intlTelInput("getSelectedCountryData");
            let code = "+" + countryData.dialCode;
            
            // Update Livewire properties
            @this.set(codeModel, code);
            
            // Call the method to update mobile length
            @this.call('CountryCodeSet', selector, code);
            
            console.log(`Country changed for ${selector} to code ${code}`);
        });
    }

   

    // Already existing
    window.addEventListener('update_input_max_length', function (event) {
        let itemId = event.detail[0].id;
        let mobile_length = event.detail[0].mobile_length;
        if (itemId && mobile_length) {
            document.querySelector(itemId).setAttribute("maxlength", mobile_length);
        }
    });
    
   window.addEventListener('set-phone-values', function (event) {

    let data = event.detail[0];

    if (data.phone_code) {

        let country = loadDialCodes(data.phone_code);

        $("#mobile").intlTelInput("setCountry", country);
    }

    if (data.phone) {
        $("#mobile").val(data.phone);
    }


    if (data.alt_phone_code_1) {

        let country = loadDialCodes(data.alt_phone_code_1);

        $("#alt_phone_1").intlTelInput("setCountry", country);
    }

    if (data.alt_phone_1) {
        $("#alt_phone_1").val(data.alt_phone_1);
    }


    if (data.alt_phone_code_2) {

        let country = loadDialCodes(data.alt_phone_code_2);

        $("#alt_phone_2").intlTelInput("setCountry", country);
    }

    if (data.alternative_phone_number_2) {
        $("#alt_phone_2").val(data.alternative_phone_number_2);
    }

});

    
</script>

@endpush