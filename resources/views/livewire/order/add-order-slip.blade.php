<div class="container">
    <section class="admin__title">
        <h5>Confirm Order</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ route('admin.order.index') }}">Orders</a></li>
            <li>Order No:- <span>#{{config('app.order_prefix') }}{{ $order->order_number }}</span></li>
            <li class="back-button">
                <a href="{{ route('admin.order.index') }}"
                    class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">Back </a>
            </li>
        </ul>
    </section>
    <form wire:submit.prevent="submitForm">
        @php
            $air_mail_price = $order->air_mail > 0 ? round($order->air_mail) : 0;
        @endphp

        <div class="card shadow-sm mb-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <h6>Order Information</h6>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0">

                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Order Amount :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">{{ number_format($order_detail->total_amount, 2) }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Order Time :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">{{ $order_detail->created_at->format('d M Y h:i A') }}</p>
                                </div>
                            </div>

                            <div class="row">

                                <div>


                                </div>
                            </div>



                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            @php
                            $hasDelivered = false;
                            foreach ($orderItemsNew as $key => $item) {
                            foreach ($item['deliveries'] as $delivery) {
                            if ($delivery['status'] == 'Delivered') {
                            $hasDelivered = true;
                            break 2; // exit both loops
                            }
                            }
                            }
                            @endphp
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Customer Details</h6>
                                @if ($hasDelivered)
                                <p>
                                    <a class="btn btn-outline-success select-md"
                                        href="{{ route('orders.generatePdf', $order_detail->id) }}"
                                        target="_blank">Download</a>
                                </p>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Person Name :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">{{ $order_detail->customer_name }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Company Name :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">
                                        {{ $order_detail->customer ? $order_detail->customer->company_name : '---' }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Rank :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">
                                        {{ $order_detail->customer ? $order_detail->customer->employee_rank : '---' }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Email :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0"> {{ $order_detail->customer_email }} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong>Mobile :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">
                                        {{ $order_detail->customer ? $order_detail->customer->phone : '' }}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="small m-0"><strong> Address :</strong></p>
                                </div>
                                <div class="col-sm-8">
                                    <p class="small m-0">{{ $order_detail->billing_address }}</p>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mb-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group mb-3">

                            <div class="accordion" id="">
                                <div class="accordion-item">
                                    <h5 class="accordion-header" id="headingInvoices">
                                        Invoice List
                                    </h5>
                                    @if (count($rest_invoices) > 0)
                                    {{-- Toggle Button --}}
                                    <div class="mt-2">
                                        <button type="button" id="toggleMoreRows"
                                            class="btn btn-sm btn-outline-primary">Show ALL</button>
                                    </div>
                                    @endif
                                    <div id="collapseInvoices" class="accordion-collapse collapse show"
                                        aria-labelledby="headingInvoices" data-bs-parent="#invoiceAccordion">
                                        <div class="accordion-body">

                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <x-table-th>Date & Time</x-table-th>
                                                        <x-table-th>Invoice No</x-table-th>
                                                        <x-table-th>Order No</x-table-th>
                                                        <x-table-th>Customer</x-table-th>
                                                        <x-table-th>Amount</x-table-th>
                                                        <x-table-th>Due Amount</x-table-th>
                                                        {{-- <x-table-th>Action</x-table-th> --}}
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach ($invoices as $key => $item)
                                                    @php
                                                    $payment_status = 'Not Paid';
                                                    $payment_class = 'danger';
                                                    if ($item->payment_status == 0) {
                                                    $payment_status = 'Not Paid';
                                                    $payment_class = 'danger';
                                                    } elseif ($item->payment_status == 1) {
                                                    $payment_status = 'Half Paid';
                                                    $payment_class = 'warning';
                                                    } elseif ($item->payment_status == 2) {
                                                    $payment_status = 'Full Paid';
                                                    $payment_class = 'success';
                                                    }
                                                    @endphp
                                                    <tr>
                                                        <x-table-td>
                                                            <p class="small text-muted mb-1 badge bg-warning">
                                                                {{-- Created At:- --}}
                                                                {{ date('d/m/Y h:i A', strtotime($item->created_at)) }}
                                                            </p>
                                                            @if (!empty($item->updated_by))
                                                            <p class="small text-muted mb-1 badge bg-warning">
                                                                {{-- Updated At:- --}}
                                                                {{ date('d/m/Y h:i A', strtotime($item->updated_at)) }}
                                                            </p>
                                                            @endif
                                                        </x-table-td>
                                                        <x-table-td>{{ 'INV/2025/' . $item->invoice_no }}
                                                        </x-table-td>
                                                        <x-table-td>
                                                            <a href="{{ route('admin.order.view', $item->order_id) }}"
                                                                class="btn btn-outline-secondary select-md btn_outline">{{
                                                                $item->order ? $item->order->order_number : '' }}</a>
                                                        </x-table-td>
                                                        <x-table-td>
                                                            <p class="small text-muted mb-1">
                                                                <span><strong>{{ ucwords($item->customer ?
                                                                        $item->customer->name : '') }}</strong>
                                                                </span>
                                                            </p>
                                                        </x-table-td>

                                                        <x-table-td>{{ number_format($item->net_price, 2) }}
                                                        </x-table-td>
                                                        <x-table-td>{{ number_format($item->due_amnt, 2) }}
                                                        </x-table-td>

                                                    
                                                    </tr>
                                                    @endforeach
                                                    {{-- show all --}}
                                                    @foreach ($rest_invoices as $key => $item)
                                                    @php
                                                    $payment_status = 'Not Paid';
                                                    $payment_class = 'danger';
                                                    if ($item->payment_status == 0) {
                                                    $payment_status = 'Not Paid';
                                                    $payment_class = 'danger';
                                                    } elseif ($item->payment_status == 1) {
                                                    $payment_status = 'Half Paid';
                                                    $payment_class = 'warning';
                                                    } elseif ($item->payment_status == 2) {
                                                    $payment_status = 'Full Paid';
                                                    $payment_class = 'success';
                                                    }
                                                    @endphp
                                                    <tr class="additional-rows d-none">
                                                        <x-table-td>
                                                            <p class="small text-muted mb-1 badge bg-warning">
                                                                {{-- Created At:- --}}
                                                                {{ date('d/m/Y h:i A', strtotime($item->created_at)) }}
                                                            </p>
                                                            @if (!empty($item->updated_by))
                                                            <p class="small text-muted mb-1 badge bg-warning">
                                                                {{-- Updated At:- --}}
                                                                {{ date('d/m/Y h:i A', strtotime($item->updated_at)) }}
                                                            </p>
                                                            @endif
                                                        </x-table-td>
                                                        <x-table-td>{{ 'INV/2025/' . $item->invoice_no }}
                                                        </x-table-td>
                                                        <x-table-td>
                                                            <a href="{{ route('admin.order.view', $item->order_id) }}"
                                                                class="btn btn-outline-secondary select-md btn_outline">{{
                                                                $item->order ? $item->order->order_number : '' }}</a>
                                                        </x-table-td>
                                                        <x-table-td>
                                                            <p class="small text-muted mb-1">
                                                                <span><strong>{{ ucwords($item->customer ?
                                                                        $item->customer->name : '') }}</strong>
                                                                </span>
                                                            </p>
                                                        </x-table-td>

                                                        <x-table-td>{{ number_format($item->net_price, 2) }}
                                                        </x-table-td>
                                                        <x-table-td>{{ number_format($item->due_amnt, 2) }}
                                                        </x-table-td>


                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>



                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row ">
                    <div class="col-md-12">
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
                        @foreach ($orderItemsNew as $key => $order_item)
                        <div class="row align-items-center mb-2 pb-3">
                            @php
                            $magrin = '';
                            if ($key != 0) {
                            $magrin = 'margin-bottom: 20px;';
                            }
                            @endphp
                            <div class="col-sm-3">
                                <table>
                                   
                                    <tr>
                                        <td>
                                            <span class="text-sm badge bg-primary sale_grn_sl" style="{{ $magrin }}">{{
                                                $key + 1 }}</span>
                                        </td>
                                        <td class="w-100">
                                            <div class="form-group mb-3">
                                                @if ($key == 0)
                                                <label>Product</label>
                                                @endif
                                                <div class="position-relative">
                                                    <input type="hidden" wire:model="order_item.{{ $key }}.price"
                                                        class="form-control form-control-sm">
                                                    <input type="hidden" wire:model="air_mail"
                                                        class="form-control form-control-sm">
                                                    <input type="hidden" wire:model="order_item.{{ $key }}.id"
                                                        class="form-control form-control-sm"
                                                        value="{{ $order_item['id'] }}">
                                                    <input type="text" value="{{ $order_item['product_name'] }}"
                                                        class="form-control form-control-sm border border-1 customer_input"
                                                        {{ $readonly }}>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                            </div>
                            @php
                            $user = auth()->guard('admin')->user();
                            @endphp
                            <div
                                class="{{ ($user->designation == 1 && $order_item['status'] == 'Process') ? 'col-sm-2' : 'col-sm-3' }}">
                                <div class="form-group mb-3">
                                    @if ($key == 0)
                                    <label>Quantity</label>
                                    @endif
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $order_item['quantity'] }}" disabled {{ $readonly }}>
                                </div>
                            </div>
                            <div
                                class="{{ ($user->designation == 1 && $order_item['status'] == 'Process') ? 'col-sm-2' : 'col-sm-3' }}">
                                <div class="form-group mb-3">
                                    @if ($key == 0)
                                    <label for="">Price</label>
                                    @endif
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $order_item['piece_price'] }}" disabled>

                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group mb-3">
                                    @if ($key == 0)
                                    <label>Status</label>
                                    @endif
                                    @php
                                    $isApprovedByTL =
                                    $order_item['status'] === 'Process' &&
                                    $order_item['tl_status'] === 'Approved';
                                    $isApprovedByAdmin =
                                    $order_item['status'] === 'Process' &&
                                    $order_item['admin_status'] === 'Approved';
                                    @endphp
                                    <input type="text"
                                        class="form-control form-control-sm text-white fw-bold rounded-pill text-center
                                                    {{ $isApprovedByAdmin ? 'bg-primary' : ($isApprovedByTL ? 'bg-info' : ($order_item['status'] === 'Process' ? 'bg-success' : 'bg-danger')) }}"
                                        value="{{ $isApprovedByAdmin ? 'Approved by SuperAdmin' : ($isApprovedByTL ? 'Approved by TL' : $order_item['status']) }}"
                                        disabled {{ $readonly }}>
                                </div>
                            </div>
                            <div class="col-sm-1">

                                @php
                                // Get the full user object once
                                $user = auth()->guard('admin')->user();

                                // Compare against full user id
                                $createdByThisAdmin = $order->created_by == $user->id;

                                $isApprovedByTL =
                                $order_item['status'] === 'Process' && $order_item['tl_status'] === 'Approved';
                                @endphp

                                {{-- Admin checkbox when TL has approved --}}
                                @if ($user->designation == 1 && $isApprovedByTL)
                                <input type="checkbox" wire:model="order_item.{{ $key }}.admin_approved"
                                     {{ $isApprovedByAdmin
                                    ? 'disabled checked' : '' }} style="cursor:pointer;">
                                @elseif($user->designation == 4)
                                {{-- TL checkbox for approving Process items --}}
                                @if ($order_item['status'] == 'Process')
                                <input type="checkbox" wire:model="order_item.{{ $key }}.tl_approved"
                                     {{ $isApprovedByAdmin ? 'disabled checked'
                                    : '' }} style="cursor:pointer;">
                                @else
                                <span class="badge bg-secondary">N/A</span>
                                @endif
                                @else
                                {{-- For others: Show tl_status as badge --}}
                                @if (!$isApprovedByTL)
                                <span
                                    class="badge {{ $order_item['tl_status'] == 'Approved' ? 'bg-success' : ($order_item['tl_status'] == 'Hold' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ $order_item['tl_status'] ?? 'Pending' }}
                                </span>
                                @endif
                                @endif
                            </div>
                            {{--  Priority dropdown (only for TL) --}}
                            @if ( in_array($user->designation,[4,1]) )
                            <div class="col-sm-2">
                                <div class="form-group mb-3">
                                    @if ($key == 0)
                                    <label>Priority <span class="text-danger">*</span></label>
                                    @endif
                                    <select wire:model="order_item.{{ $key }}.priority_level"
                                        class="form-control form-control-sm @error('order_item.'.$key.'.priority_level') is-invalid @enderror">
                                        <option value="" selected hidden>Select Priority</option>
                                        <option value="Priority">Priority</option>
                                        <option value="Non Priority">Non Priority</option>
                                    </select>
                                     @error('order_item.'.$key.'.priority_level')
                                        <small class="text-danger error-message">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            @endif
                            {{-- Team Dropdown start --}}
                            {{-- Only Admin Can select the team --}}
                            @if ($user->designation == 1 && $order_item['status'] == 'Process')
                            <div class="col-sm-2">
                                <div class="form-group mb-3">
                                    @if ($key == 0)
                                    <label>Delivery</label>
                                    @endif
                                    <select wire:model="order_item.{{ $key }}.team" class="form-control form-control-sm"
                                        {{-- @if ($this->order_item[$key]['team'])
                                        disabled
                                        @endif --}}
                                        >
                                        <option value="" selected hidden>Select Team</option>
                                        <option value="sales">Sales Team</option>
                                        <option value="production">Production Team</option>
                                    </select>

                                </div>
                            </div>
                            @endif
                            {{-- Team Dropdown end --}}
                            {{-- Start the measurement section --}}
                            @if ($order_item['collection_id'] == 1 && !empty($order_item['measurements']))
                            <div class="row mt-4 mb-4">
                                <div class="col-md-12">

                                    <h6 class="badge bg-danger custom_success_badge">Measurements</h6>
                                </div>
                                <div class="col-sm-6">


                                    @php
                                        $measurements = collect($order_item['measurements'])
                                        ->unique('measurement_name')
                                        ->mapWithKeys(function ($m) {
                                            return [
                                                $m['measurement_name'] . ' [' . $m['measurement_title_prefix'] . ']' => [
                                                    'value' => $m['measurement_value'],
                                                    'remarks' => $m['measurement_remarks'] ?? null,
                                                ]
                                            ];
                                        });
                                        $chunks = array_chunk($measurements->toArray(), 5, true);
                                    @endphp

                                   
                                        <div class="row">
                                            @foreach ($chunks as $row)
                                         
                                                @foreach ($row as $label => $data)
                                               
                                                    <div class="col-md-3">
                                                       
                                                        <label for="">{{ $label }}</label>
                                                        <input type="text"
                                                            class="form-control form-control-sm border border-1 customer_input text-center measurement_input"
                                                            readonly value=" {{ $data['value'] }}">

                                                        @if(!empty($data['remarks']))
                                                            <textarea class="form-control form-control-sm bg-white text-muted mt-2" 
                                                              rows="2" 
                                                              readonly 
                                                              style="resize: none; font-size: 0.8rem;">{{ $data['remarks'] }}</textarea>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                
                                                @for ($i = count($row); $i < 5; $i++) <td>
                                                </td>
                                                @endfor
                                              
                                            @endforeach
                                        </div>
                                        
                                       @if(!empty($order_item['remarks']))
                                        <div class="mt-3">
                                            <label for="remarks"><strong>Remarks:</strong></label>
                                           <div class="form-control form-control-sm border border-1 bg-light text-muted" 
 style="min-height: 60px; height: auto; white-space: pre-wrap; overflow: hidden;">
                                               {{$order_item['remarks']}}
                                             </div>
                                        </div>
                                        @endif
                                      
                                </div>
                               
                                <div class="col-sm-3">
                                    <p><strong>Fabric:</strong> {{ $order_item['fabrics']->title ?? 'N/A' }}</p>
                                    <p><strong>Catalogue:</strong>
                                        {{ optional(optional($order_item['catalogue'])->catalogueTitle)->title ?? 'N/A'
                                        }}
                                        (Page: {{ $order_item['cat_page_number'] ?? 'N/A' }}
                                            @if (!empty($order_item['cat_page_item']))
                                               (<strong>{{$order_item['cat_page_item'] ?? 'N/A'}}</strong>)     
                                            @endif
                                        )
                                    </p>
                                    
                                    <p>
                                        Expected Delivery Month :
                                        <strong>
                                            {{ !empty($order_item['expected_delivery_date']) 
                                                        ? \Carbon\Carbon::parse($order_item['expected_delivery_date'])->format('F, Y') 
                                                        : 'N/A' }}
                                        </strong>
                                    </p>
                                    <p>Fittings : <strong>{{$order_item['fittings'] ?? 'N/A'}}</strong></p>
                                    <p>Priority Level : <strong>{{$order_item->priority_level ?? 'N/A'}}</strong></p>

                                    {{-- Catalogue images --}}
                                    @if(!empty($order_item['catlogue_image']))
                                    <div class="catelog-wrap mt-3">
                                        <p> <i class="fas fa-image"></i> CATALOGUE IMAGES :</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($order_item['catlogue_image'] as $img)
                                            <a target="_blank" href="{{ asset('storage/'.$img->image_path) }}">
                                                <img src="{{ asset('storage/'.$img->image_path) }}"
                                                    class="img-fluid rounded shadow border border-secondary"
                                                    style="width:50px;height:50px;" alt="Catalogue image">
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    {{-- @dd($item['voice_remarks']) --}}
                                    @if(!empty($order_item['voice_remark']))
                                    <p><i class="fas fa-microphone"></i> Voice Remarks</p>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach ($order_item['voice_remark'] as $voice)
                                        {{-- @dd($voice) --}}
                                        <audio controls>
                                            <source src="{{ asset('storage/'.$voice->voices_path) }}" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="col-sm-3">
                                  @if(in_array('mens_jacket_suit', $order_item['extra_type'] ?? []))
                                    <p><strong>Hand Stitching:</strong> {{ $item['mens_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                    <p><strong>Shoulder Type:</strong> {{ $order_item['shoulder_type'] ?? 'N/A' }}</p>
                                    <p><strong>Vents:</strong> {{ $order_item['vents'] ?? 'N/A' }}</p>
                                    @endif
                                    
                                   @if(in_array('ladies_jacket_suit', $order_item['extra_type'] ?? []))
                                    <p><strong>Hand Stitching:</strong> {{ $item['ladies_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                    <p><strong>Shoulder Type:</strong> {{ $order_item['shoulder_type'] ?? 'N/A'
                                        }}</p>
                                    <p><strong>Vents Required:</strong> {{ $order_item['vents_required'] ?? 'N/A'
                                        }}</p>
                                    <p><strong>Vents Count:</strong> {{ $order_item['vents_count'] ?? 'N/A' }}</p>
                                    @endif
                                    
                                    @if(in_array('trouser', $order_item['extra_type'] ?? []))
                                    <p><strong>Fold Cuff Required:</strong> {{ $order_item['fold_cuff_required']
                                        ?? 'N/A' }}</p>
                                    <p><strong>Fold Cuff Size:</strong> {{ $order_item['fold_cuff_size'] ?
                                        $order_item['fold_cuff_size'] . ' cm' : 'N/A'
                                        }}</p>
                                    <p><strong>Pleats Required:</strong> {{ $order_item['pleats_required'] ??
                                        'N/A' }}</p>
                                   
                                    <p><strong>Back Pocket Required:</strong> {{
                                        $order_item['back_pocket_required'] ?? 'N/A' }}</p>
                                    
                                    <p><strong>Adjustable Belt:</strong> {{ $order_item['adjustable_belt'] ??
                                        'N/A' }}</p>
                                    <p><strong>Suspender Button:</strong> {{ $order_item['suspender_button'] ??
                                        'N/A' }}</p>
                                    <p><strong>Trouser Position:</strong> {{ $order_item['trouser_position'] ??
                                        'N/A' }}</p>
                                    @endif
                                    @if(
                                        in_array('ladies_jacket_suit', $order_item['extra_type'] ?? []) ||
                                        in_array('trouser', $order_item['extra_type'] ?? []) ||
                                        in_array('mens_jacket_suit', $order_item['extra_type'] ?? [])
                                    )

                                    <p><strong>Client Name Required:</strong> {{ $order_item['client_name_required'] ??
                                        'N/A' }}</p>
                                    <p><strong>Client Name Place:</strong> {{ $order_item['client_name_place'] ??
                                        'N/A' }}</p>
                                    <p><strong>Client Name Options:</strong> {{ $order_item['client_name_options'] ??
                                        'N/A' }}</p>
                                    @endif
                                </div>
                            </div>
                            @else
                               <tr>
                                    <td colspan="6">
                                        <div class="td-details">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="row">
                                                        @if(!empty($order_item['remarks']))
                                                                <div>
                                                                    <label for="remarks"><strong>Remarks:</strong></label>
                                                                   <div class="form-control form-control-sm border border-1 bg-light text-muted" 
                                                                      style="min-height: 60px; height: auto; white-space: pre-wrap; overflow: hidden;">
                                                                       {{$order_item['remarks']}}
                                                                    </div>
                                                                </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <p>Expected Delivery Date : <strong>{{$order_item['expected_delivery_date'] ??
                                                        'N/A'}}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                                
                                    </td>
                                </tr>
                            @endif
                            @endforeach


                            {{-- Air mail --}}
                            @if ($order->air_mail > 0)
                             
                            <div class="col-sm-6">
                                <table>
                                    <tr>
                                        <td>

                                        </td>
                                        <td class="w-100">
                                            <div class="form-group mb-3">
                                                <label>AIR MAIL</label>
                                                <div class="position-relative">
                                                    <input type="text" value="AIR MAIL"
                                                        class="form-control form-control-sm border border-1 customer_input"
                                                        readonly>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>



                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Price</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $air_mail_price }}" readonly>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            {{--  @php
                $hasAdminApproved = $order->items->contains(function($item) {
                    return $item->admin_status === 'Approved';
                });
            
                $hasTLNotApproved = $order->items->contains(function($item) {
                    return $item->tl_status !== 'Approved';
                });
            
                $hasTLApproved = $order->items->contains(function($item) {
                    return $item->tl_status === 'Approved';
                });
            @endphp  --}}

                <div class="row">
                    <div class="form-group text-end">
                        <span>ORDER AMOUNT <span class="text-danger">
                           @if($air_mail_price > 0)
                                ({{ number_format($actual_amount + $air_mail_price, 2) }})
                            @else
                                ({{ number_format($actual_amount, 2) }})
                            @endif
                        </span></span>
                        @if ($user && $user->designation == 1)
                        <button wire:click.prevent="setTeamAndSubmit" class="btn btn-sm btn-success">Approve
                            Order</button>
                        @else
                        <button type="submit" id="submit_btn" class="btn btn-sm btn-success"><i
                                class="material-icons text-white" style="font-size: 15px;">add</i>Confirm
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>


    </form>
</div>
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('livewire:init', function () {
    Livewire.on('scroll-to-error', () => {
        setTimeout(() => {
            // Find either the validation input or the closest error text container
            let el = document.querySelector('.is-invalid');

            if (!el) {
                el = document.querySelector('.error-message');
            }

            if (!el) return;

            // Target the parent form group context if possible, for better centering visuals
            const targetedElement = el.closest('.form-group') || el;

            const elementRect = targetedElement.getBoundingClientRect();
            const absoluteElementTop = elementRect.top + window.pageYOffset;
            
            // Calculate coordinates to center the field precisely on the screen
            const centerPosition = absoluteElementTop - (window.innerHeight / 2) + (elementRect.height / 2);

            window.scrollTo({
                top: centerPosition,
                behavior: 'smooth'
            });

            // Focus on the input if it's focusable
            const inputField = el.classList.contains('is-invalid') ? el : el.closest('.form-group')?.querySelector('select, input');
            if (inputField && typeof inputField.focus === 'function') {
                inputField.focus({ preventScroll: true });
            }

        }, 150);
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleMoreRows');
        const additionalRows = document.querySelectorAll('.additional-rows');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const isHidden = additionalRows[0]?.classList.contains('d-none');

                additionalRows.forEach(row => {
                    row.classList.toggle('d-none');
                });

                toggleBtn.textContent = isHidden ? 'Show Less' : 'Show ALL';
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleMoreRows');
        const additionalRows = document.querySelectorAll('.additional-rows');

        toggleBtn.addEventListener('click', function () {
            //  event.preventDefault(); 
            const isHidden = additionalRows[0]?.classList.contains('d-none');

            additionalRows.forEach(row => {
                row.classList.toggle('d-none');
            });

            toggleBtn.textContent = isHidden ? 'Show Less' : 'Show ALL';
        });
    });
</script>

@endpush