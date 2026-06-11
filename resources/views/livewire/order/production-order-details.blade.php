<div class="container">
    <style>
        .no-padding-td tr td {
            padding: 12px 6px !important;
        }

        .audio-stack {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .audio-stack audio {
            max-width: 214px;
        }

        .catelog-wrap {
            margin-bottom: 25px;
        }
    </style>


    <section class="admin__title">
        <h5>Order detail</h5>
    </section>
    <section>

        <ul class="breadcrumb_menu">
            <li><a href="{{route('admin.order.index')}}">Orders</a></li>
            <li>Order detail :- <span>#{{config('app.order_prefix') }}{{$order->order_number}}</span></li>
            <li class="back-button">
                <a href="{{route('production.order.index')}}"
                    class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">Back </a>
            </li>
        </ul>
    </section>
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
                                <p class="small m-0"><strong>Order Time :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{ $order->created_at->format('d M Y h:i A') }}</p>
                            </div>
                        </div>
                        {{-- Client Image --}}
                        <div class="row mb-1">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Client Image :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                @php 
                                    $clientImages = $order->files->where('file_type', 'customer_image');
                                    $clientImagePaths = [];
                                    foreach($clientImages as $file) {
                                        $paths = explode(',', $file->file_path);
                                        $clientImagePaths = array_merge($clientImagePaths, $paths);
                                    }
                                    $clientImagePaths = array_filter($clientImagePaths); // remove empty
                                @endphp
                        
                                @if(count($clientImagePaths))
                                    @foreach($clientImagePaths as $index => $path)
                                        <a data-fancybox="client-images"
                                           href="{{ asset(trim($path)) }}"
                                           @if($index > 0) style="display:none" @endif
                                           class="{{ $index === 0 ? 'btn btn-outline-primary btn-sm' : '' }}">
                                           {{ $index === 0 ? 'View' : '' }}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No Image</span>
                                @endif
                            </div>
                        </div>
                        
                        {{-- PHYSICAL BILL BOOK --}}
                     {{--   <div class="row mb-1">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Physical Bill Book :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                @php 
                                    $billBooks = $order->files->where('file_type', 'bill_book_copy');
                                    $billBookPaths = [];
                                    foreach($billBooks as $file) {
                                        $paths = explode(',', $file->file_path);
                                        $billBookPaths = array_merge($billBookPaths, $paths);
                                    }
                                    $billBookPaths = array_filter($billBookPaths);
                                @endphp
                        
                                @if(count($billBookPaths))
                                    @foreach($billBookPaths as $index => $path)
                                        <a data-fancybox="bill-books"
                                           href="{{ asset(trim($path)) }}"
                                           @if($index > 0) style="display:none" @endif
                                           class="{{ $index === 0 ? 'btn btn-outline-primary btn-sm' : '' }}">
                                           {{ $index === 0 ? 'View' : '' }}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No Bill</span>
                                @endif
                            </div>
                        </div> --}}
                        
                        {{-- VERIFIED VIDEO --}}
                        <div class="row mb-1">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Verified Video :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                @php 
                                    $videos = $order->files->where('file_type', 'verified_video');
                                    $videoPaths = [];
                                    foreach($videos as $file) {
                                        $paths = explode(',', $file->file_path);
                                        $videoPaths = array_merge($videoPaths, $paths);
                                    }
                                    $videoPaths = array_filter($videoPaths);
                                @endphp
                        
                                @if(count($videoPaths))
                                    @foreach($videoPaths as $index => $path)
                                        <a data-fancybox="verified-video"
                                           href="{{ asset(trim($path)) }}"
                                           @if($index > 0) style="display:none" @endif
                                           class="{{ $index === 0 ? 'btn btn-outline-primary btn-sm' : '' }}">
                                           {{ $index === 0 ? 'View' : '' }}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No Video</span>
                                @endif
                            </div>
                        </div>
                        
                        {{-- VERIFIED AUDIO --}}
                        <div class="row mb-1">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Verified Audio :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                @php 
                                    $audios = $order->files->where('file_type', 'verified_audio');
                                    $audioPaths = [];
                                    foreach($audios as $file) {
                                        $paths = explode(',', $file->file_path);
                                        $audioPaths = array_merge($audioPaths, $paths);
                                    }
                                    $audioPaths = array_filter(array_map('trim', $audioPaths));
                                @endphp
                        
                                @if(count($audioPaths))
                                    @foreach($audioPaths as $index => $path)
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="fas fa-music text-primary"></i>
                                            <audio controls style="height:30px; max-width:220px;">
                                                <source src="{{ asset(trim($path)) }}">
                                            </audio>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No Audio</span>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Fittings --}}
                        {{-- <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Fittings :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <a href="{{asset($order->customer_image)}}" target="_blank">
                                    <img src="{{asset($order->customer_image)}}" alt="no-img" width="100">
                                </a>
                            </div>
                        </div> --}}



                    </div>
                </div>
                {{-- <div class="col-sm-6">
                    <div class="form-group mb-3">
                        <h6>Customer Details</h6>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Person Name :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{$order->customer_name}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Company Name :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{$order->customer?$order->customer->company_name:"---"}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Rank :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{$order->customer?$order->customer->employee_rank:"---"}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Email :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$order->customer_email}} </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong>Mobile :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0"> {{$order->customer? $order->customer->phone: ""}}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4">
                                <p class="small m-0"><strong> Address :</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="small m-0">{{$order->billing_address}}</p>
                            </div>
                        </div>


                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <div class="card-body">
                <table class="table table-sm ledger no-padding-td">
                    <thead>
                        <tr>
                            <th class="" rowspan="1" colspan="1" style="width: 65px;" aria-label="price">Collection</th>
                            <th class="w-50 " rowspan="1" colspan="1" style="width: 328px;" aria-label="products">Order
                                Items</th>
                            {{-- <th class="" rowspan="1" colspan="1" style="width: 65px;" aria-label="price">price</th>
                            --}}
                            <th class="" rowspan="1" colspan="1" style="width: 50px;" aria-label="qty">
                                qty</th>
                            <th class="" rowspan="1" colspan="1" style="width: 50px;" aria-label="used">
                                Total Used</th>
                            <th class="" rowspan="1" colspan="1" style="width: 50px;" aria-label="">
                                Action</th>
                            {{-- <th class="" rowspan="1" colspan="1" style="width: 80px;" aria-label="total">total</th>
                            --}}
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($orderItems) > 0)

                        @foreach ($orderItems as $item)
                        {{-- {{dd()}} --}}
                        <tr class="odd" style="background-color: #f2f2f2;">
                            <td>{{$item['collection_title']}}</td>
                            <td class="">
                                <div class="d-flex justify-content-start align-items-center product-name">
                                    <div class="me-3">
                                        @if (!empty($item['product_image']))
                                        <div class="avatar avatar-sm rounded-2 bg-label-secondary">
                                            <img src="{{ asset('storage/' . $item['product_image']) }}"
                                                alt="Product Image" class="rounded-2">
                                        </div>
                                        @else
                                        <div class="avatar avatar-sm rounded-2 bg-label-secondary">
                                            <img src="{{asset('assets/img/cubes.png')}}" alt="Default Image"
                                                class="rounded-2">
                                        </div>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="text-nowrap text-heading fw-medium">{{$item['product_name']}}</span>
                                    </div>
                                </div>
                            </td>
                            {{-- <td><span>{{number_format($item['price'], 2)}}</span></td> --}}
                            <td><span>{{$item['quantity']}}</span></td>
                            @if ($item['has_stock_entry'])

                            <td>
                                @php
                                $inputName = 'row_' . $loop->index . '_' . $item['stock_entry_data']['input_name'];
                                $required = $item['stock_entry_data']['available_value'] ?? 0;
                                $totalUsed = $item['total_used'] ?? 0;
                                $isFabric = $item['collection_id'] == 1;
                                $unit = $isFabric ? 'meters' : 'pieces';
                                @endphp
                                <span>{{$totalUsed}} {{$unit}}</span>


                            </td>
                            @else
                            <td>0</td>
                            @endif
                            <td>
                                <div>
                                    {{-- @if ($item['collection_id'] == 1)

                                    @endif --}}

                                    @if ($item['collection_id'] == 1)
                                    @if ($item['is_delivered'] ?? false)
                                    <button class="btn btn-success select-md" disabled>
                                        Delivered
                                    </button>
                                    {{-- View log button --}}
                                    <button class="btn btn-outline-info select-md" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ $item['logs'] }}">
                                        View Log
                                    </button>
                                    @else
                                    <button class="btn btn-outline-success select-md"
                                        wire:click="openStockModal({{ $item['id'] }})">
                                        @if ($item['has_stock_entry'])
                                        Update Stock
                                        @else
                                        Enter Stock
                                        @endif
                                    </button>
                                    @if ($item['has_stock_entry'])
                                    <button id="delivery-btn-{{ $loop->index }}"
                                        class="btn btn-outline-success select-md"
                                        wire:click="openGarmentDeliveryModal({{ $item['id'] }})">
                                        Delivery
                                    </button>
                                    @endif
                                    @endif
                                    @endif

                                    @if ($item['collection_id'] == 2)
                                    @php
                                    $orderedQty = (int) ($item['quantity'] ?? 0) ;
                                    $deliveredQty = (int) ($item['delivered_quantity'] ?? 0);
                                    // dd($deliveredQty);
                                    $remainingQty = $orderedQty - $deliveredQty;
                                    @endphp
                                    @if ($remainingQty <=0 ) <button class="btn btn-success select-md" disabled>
                                        Delivered
                                        </button>
                                        <button class="btn btn-outline-info select-md" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="{{ $item['logs'] }}">
                                            View Log
                                        </button>
                                        @else
                                        <button class="btn btn-outline-success select-md"
                                            wire:click="openDeliveryModal({{ $loop->index }})">
                                            Delivery
                                        </button>
                                        @endif
                                        @endif

                                </div>
                            </td>
                        </tr>
                        @if($item['collection_id']==1)
                        <tr>
                            <td colspan="6">
                                <div class="td-details">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="badge bg-danger custom_success_badge">Measurements</h6>
                                            <div class="row">

                                                @foreach ($item['measurements'] as $index => $measurement)
                                                <div class="col-md-3">
                                                    <label>
                                                        {{$measurement['measurement_name']}}
                                                        <strong
                                                            style="display:block;">[{{$measurement['measurement_title_prefix']}}]</strong>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control form-control-sm border border-1 customer_input text-center measurement_input"
                                                        readonly value="{{ $measurement['measurement_value'] }}">
                                                        
                                                    @if(!empty($measurement['measurement_remarks']))
                                                        <textarea class="form-control form-control-sm bg-white text-muted mt-2" 
                                                          rows="2" 
                                                          readonly 
                                                          style="resize: none; font-size: 0.8rem;">{{ $measurement['measurement_remarks'] }}</textarea>
                                                    @endif    
                                                </div>
                                                @endforeach

                                            </div>
                                            {{-- Remarks --}}
                                            @if (!empty($item['remarks']))
                                            <div class="mt-3">
                                                <label for="remarks"><strong>Remarks:</strong></label>
                                                <textarea readonly class="form-control form-control-sm border border-1"
                                                    rows="3">{{ $item['remarks'] }}</textarea>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <p>FABRIC : <strong>{{$item['fabrics']->title ?? 'N/A'}}</strong></p>
                                            <p>CATALOGUE : <strong>{{
                                                    optional(optional($item['catalogue'])->catalogueTitle)->title
                                                    }}</strong> 
                                                    <br/>
                                                    (PAGE:
                                                <strong>{{$item['cat_page_number']}}</strong>
                                                
                                                    @if (!empty($item['cat_page_item']))
                                                       (<strong>{{$item['cat_page_item'] ?? 'N/A'}}</strong>)     
                                                    @endif
                                                )
                                            </p>
                                            <p>
                                                Expected Delivery Month :
                                                <strong>
                                                    {{ !empty($item['expected_delivery_date']) 
                                                        ? \Carbon\Carbon::createFromFormat('Y-m', $item['expected_delivery_date'])->format('F, Y') 
                                                        : 'N/A' }}
                                                </strong>
                                            </p>
                                            <p>Fittings : <strong>{{$item['fittings']}}</strong></p>
                                            <p>Priority Level: <strong>{{$item['priority']}}</strong></p>

                                            {{-- Catalogue images --}}
                                            @if(!empty($item['catlogue_images']))
                                            <div class="catelog-wrap">
                                                <p>CATALOGUE IMAGES :</p>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($item['catlogue_images'] as $img)
                                                    <a target="_blank" href="{{ asset('storage/'.$img->image_path) }}">
                                                        <img src="{{ asset('storage/'.$img->image_path) }}"
                                                            class="img-fluid rounded shadow border border-secondary"
                                                            style="width:50px;height:50px;" alt="Catalogue image">
                                                    </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif

                                            @if(!empty($item['voice_remarks']))
                                            <p>VOICE REMARKS : </p>
                                            <div class="audio-stack">
                                                @foreach ($item['voice_remarks'] as $voice)
                                                <audio controls>
                                                    <source src="{{ asset('storage/'.$voice->voices_path) }}"
                                                        type="audio/mpeg">
                                                    Your browser does not support the audio element.
                                                </audio>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            @if(in_array('mens_jacket_suit', $item['extra_type'] ?? []))
                                            <p><strong>Hand Stitching:</strong> {{ $item['mens_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                            <p><strong>Shoulder Type:</strong> {{ $item['shoulder_type'] ?? 'N/A' }}</p>
                                            <p><strong>Vents:</strong> {{ $item['vents'] ?? 'N/A' }}</p>
                                            @endif

                                             @if(in_array('ladies_jacket_suit', $item['extra_type'] ?? []))
                                             <p><strong>Hand Stitching:</strong> {{ $item['ladies_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                            <p><strong>Shoulder Type:</strong> {{ $item['shoulder_type'] ?? 'N/A' }}</p>
                                            <p><strong>Vents Required:</strong> {{ $item['vents_required'] ?? 'N/A'
                                                }}</p>
                                            <p><strong>Vents Count:</strong> {{ $item['vents_count'] ?? 'N/A' }}</p>
                                            @endif


                                              @if(in_array('trouser', $item['extra_type'] ?? []))
                                            <p><strong>Fold Cuff Required:</strong> {{ $item['fold_cuff_required']
                                                ?? 'N/A' }}</p>
                                            <p><strong>Fold Cuff Size:</strong> {{ $item['fold_cuff_size'] ?
                                                $item['fold_cuff_size'] . ' cm' : 'N/A'
                                                }}</p>
                                            <p><strong>Pleats Required:</strong> {{ $item['pleats_required'] ??
                                                'N/A' }}</p>
                                           
                                            <p><strong>Back Pocket Required:</strong> {{
                                                $item['back_pocket_required'] ?? 'N/A' }}</p>
                                           
                                            <p><strong>Adjustable Belt:</strong> {{ $item['adjustable_belt'] ??
                                                'N/A' }}</p>
                                            <p><strong>Suspender Button:</strong> {{ $item['suspender_button'] ??
                                                'N/A' }}</p>
                                            <p><strong>Trouser Position:</strong> {{ $item['trouser_position'] ??
                                                'N/A' }}</p>
                                            @endif
                                            
                                             @if(
                                                in_array('ladies_jacket_suit', $item['extra_type'] ?? []) ||
                                                in_array('shirt', $item['extra_type'] ?? []) ||
                                                in_array('mens_jacket_suit', $item['extra_type'] ?? [])
                                            )
                                            <p><strong>Client Name Required:</strong> {{ $item['client_name_required']
                                                ??
                                                'N/A' }}</p>
                                            <p><strong>Client Name Place:</strong> {{ $item['client_name_place'] ??
                                                'N/A' }}</p>
                                            <p><strong>Client Name Options:</strong> {{ $item['client_name_options'] ??
                                                'N/A' }}</p>    
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                        </tr>
                        @else
                        <tr>
                            <td colspan="4" class="pt-4" style="vertical-align: text-top !important;">
                                <div class="row">
                                    <div class="col-md-6">
                                        @if (!empty($item['remarks']))
                                        <div class="">
                                            <label for="remarks"><strong>Remarks:</strong></label>
                                            <textarea readonly class="form-control form-control-sm border border-1"
                                                rows="3">{{ $item['remarks'] }}</textarea>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <p>Expected Delivery Date : <strong>{{$item['expected_delivery_date']}}</strong>
                                        </p>
                                        <p>Priority Level: <strong>{{$item['priority']}}</strong></p>
                                    </div>
                                </div>


                            </td>
                        </tr>
                        @endif

                        @endforeach
                        @else
                        <tr>
                            <td>
                                <p>No items found for this order.</p>
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>

                {{-- Stock Entry Modal --}}
                <div wire:ignore.self class="modal fade" id="stockEntryModal" tabindex="-1"
                    aria-labelledby="stockEntryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="stockEntryModalLabel">
                                    Stock Entry Sheet - {{$order->order_number}}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @if ($selectedItem)
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Stock Entry Interface</h6>
                                        @foreach ($stockEntries as $entryIndex => $entry)
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">
                                                    <strong>Collection</strong> <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" value="{{ $selectedItem['collection_title'] ?? '' }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">
                                                    <strong>Fabric</strong> <span class="text-danger">*</span>
                                                </label>
                                                @if ($entryIndex === 0)
                                                {{-- First row: show disabled input --}}
                                                <input type="text"
                                                    value="{{ $selectedItem['collection_id'] == 2 ? $selectedItem['product_name'] : $selectedItem['fabric_title'] }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @elseif($selectedItem['collection_id'] == 1)
                                                {{-- Fabric Search Input --}}
                                                <div class="mb-2">
                                                    <input type="text" wire:model.lazy="fabricSearch.{{ $entryIndex }}"
                                                        class="form-control form-control-sm"
                                                        placeholder="Search fabric..."
                                                        wire:keyup="searchFabric({{ $entryIndex }})">
                                                </div>
                                                {{-- Fabric Select Dropdown --}}
                                                @if (!empty($searchResults[$entryIndex]))
                                                <div class="dropdown-menu show w-100"
                                                    style="max-height: 187px; max-width: 100px; overflow-y: auto;">
                                                    @foreach ($searchResults[$entryIndex] as $fabric)
                                                    <button class="dropdown-item fabric_dropdown_item" type="button"
                                                        wire:click="selectFabric({{ $fabric['id'] }}, {{ $entryIndex }})">
                                                        {{ $fabric['title'] }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                                @endif

                                                @error('stockEntries.' . $entryIndex . '.fabric_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">{{ $selectedItem['available_label'] }}</label>
                                                @if ($entryIndex === 0)
                                                <input type="number" value="{{ $selectedItem['available_value'] }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @else
                                                <input type="number"
                                                    wire:model="stockEntries.{{ $entryIndex }}.available_value"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @endif
                                            </div>
                                            {{-- Required meter --}}
                                            <div class="col-md-2">
                                                <label class="form-label">{{ $selectedItem['updated_label'] }}</label>

                                                @php
                                                // Always use consistent key for validation and binding
                                                $inputName = 'required_meter_' . $entryIndex;
                                                $fullInputKey = "rows.$inputName";
                                                @endphp

                                                <input type="text" wire:model="{{ $fullInputKey }}"
                                                    class="form-control form-control-sm border border-1 p-2 @error($fullInputKey) is-invalid @enderror">

                                                @error($fullInputKey)
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-1 mt-4">
                                                {{-- <button class="btn btn-outline-success select-md" wire:click="updateStock({{ $selectedItem['item_id'] }},
                                                            '{{ $selectedItem['input_name'] }}')">
                                                    Update
                                                </button> --}}
                                                @if($selectedItem['has_stock_entry'])
                                                <button class="btn btn-outline-danger select-md" wire:click="$dispatch('confirm-revert-back', {
                                                    itemId: {{ $selectedItem['item_id'] }},
                                                    inputName: '{{ $selectedItem['input_name'] }}',
                                                    entryId: {{ $entry['id'] ?? 'null' }}
                                                })">
                                                    Revert Back
                                                </button>
                                                @endif
                                            </div>

                                            @if ($entryIndex !== 0)
                                            <div class="col-md-1">
                                                <button class="btn btn-danger btn-sm ms-2 mt-3"
                                                    wire:click="removeStockEntry('{{ $entryIndex }}')">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="mb-3">
                                                <button type="button" class="btn btn-outline-success select-md"
                                                    wire:click="addStockEntry"><i class="material-icons me-1">add</i>Add
                                                    More</button>
                                            </div>
                                            {{-- Update All --}}
                                            <div class="mb-3">
                                                <button class="btn btn-outline-success select-md"
                                                    wire:click="updateStock({{ $selectedItem['item_id'] }})">
                                                    Update All
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                @else
                                <p class="text-muted">No data loaded.</p>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Delivery Process Modal For Garment --}}
                <div wire:ignore.self class="modal fade" id="delieveryGarmentProcessModal" tabindex="-1"
                    aria-labelledby="delieveryGarmentProcessModal" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="delieveryGarmentProcessModal">Process Delivery -
                                    {{$order->order_number}}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @if ($selectedItem)
                                <div class="card">
                                    <div class="card-body">
                                        {{-- <h6>Stock </h6> --}}
                                        @foreach ($stockEntries as $entryIndex => $entry)
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="form-label">
                                                    <strong>Collection</strong> <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" value="{{ $selectedItem['collection_title'] ?? '' }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">
                                                    <strong>Fabric</strong> <span class="text-danger">*</span>
                                                </label>
                                                @if ($entryIndex === 0)
                                                {{-- First row: show disabled input --}}
                                                <input type="text"
                                                    value="{{ $selectedItem['collection_id'] == 2 ? $selectedItem['product_name'] : $selectedItem['fabric_title'] }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @elseif($selectedItem['collection_id'] == 1)
                                                {{-- Fabric Search Input --}}
                                                <div class="mb-2">
                                                    <input type="text" wire:model.lazy="fabricSearch.{{ $entryIndex }}"
                                                        class="form-control form-control-sm"
                                                        placeholder="Search fabric..."
                                                        wire:keyup="searchFabric({{ $entryIndex }})" disabled>
                                                </div>
                                                {{-- Fabric Select Dropdown --}}
                                                @if (!empty($searchResults[$entryIndex]))
                                                <div class="dropdown-menu show w-100"
                                                    style="max-height: 187px; max-width: 100px; overflow-y: auto;">
                                                    @foreach ($searchResults[$entryIndex] as $fabric)
                                                    <button class="dropdown-item fabric_dropdown_item" type="button"
                                                        wire:click="selectFabric({{ $fabric['id'] }}, {{ $entryIndex }})">
                                                        {{ $fabric['title'] }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                                @endif

                                                @error('stockEntries.' . $entryIndex . '.fabric_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">{{ $selectedItem['available_label'] }}</label>
                                                @if ($entryIndex === 0)
                                                <input type="number" value="{{ $selectedItem['available_value'] }}"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @else
                                                <input type="number"
                                                    wire:model="stockEntries.{{ $entryIndex }}.available_value"
                                                    class="form-control form-control-sm border border-1 p-2" disabled>
                                                @endif
                                            </div>
                                            {{-- Required meter --}}
                                            <div class="col-md-2">
                                                <label class="form-label">{{ $selectedItem['updated_label'] }}</label>

                                                @php
                                                // Always use consistent key for validation and binding
                                                $inputName = 'required_meter_' . $entryIndex;
                                                $fullInputKey = "rows.$inputName";
                                                @endphp

                                                <input type="text" disabled wire:model="{{ $fullInputKey }}"
                                                    class="form-control form-control-sm border border-1 p-2 @error($fullInputKey) is-invalid @enderror">

                                                @error($fullInputKey)
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            {{-- NEW -> Delivered meter (what team is handing out now) --}}
                                            <div class="col-md-2">
                                                <label class="form-label">Extra&nbsp;Meter</label>
                                                <input type="number"
                                                    wire:model="deliveryEntries.{{ $entryIndex }}.delivered_meter"
                                                    class="form-control form-control-sm border border-1 p-2">
                                                @error('deliveryEntries.' . $entryIndex . '.delivered_meter')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                                @if (session()->has('stock_error'))
                                                <p class="small text-danger">{{ session('stock_error') }}</p>
                                                @endif
                                            </div>
                                            {{-- Row‑level Add Extra button --}}
                                            <div class="col-md-2 mt-4">
                                                <button type="button" class="btn btn-outline-success select-md"
                                                    wire:click="addDeliveryRow({{ $entryIndex }})">
                                                    + Add Extra
                                                </button>
                                            </div>

                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <p class="text-muted">No data loaded.</p>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-dark"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="processDelivery">
                                    Process Delivery
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Delivery Process Modal For Garment Item--}}
                <div wire:ignore.self class="modal fade" id="delieveryProcessModal" tabindex="-1"
                    aria-labelledby="delieveryProcessModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="delieveryProcessModalLabel">Process Delivery -
                                    {{$order->order_number}}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- Row for Stock Validation -->
                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-6">
                                                @if (isset($selectedDeliveryItem['collection_id']) &&
                                                $selectedDeliveryItem['collection_id'] == 1)
                                                <strong>Total Usage:</strong>
                                                {{ $selectedDeliveryItem['planned_usage'] ?? 0 }} {{
                                                $selectedDeliveryItem['unit'] ?? '' }}
                                                @else
                                                <strong>Total Stock:</strong>
                                                {{ $selectedDeliveryItem['stock_product'] ?? 0 }} {{
                                                $selectedDeliveryItem['unit'] ?? '' }}
                                                <br>
                                                <strong>Delivered Quantity:</strong>
                                                {{ $selectedDeliveryItem['delivered_quantity'] ?? 0 }} {{
                                                $selectedDeliveryItem['unit'] ?? '' }}
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label for="actualUsage" class="form-label mb-1">Actual Usage ({{
                                                    $selectedDeliveryItem['unit'] ?? '' }})</label>
                                                <input type="number"
                                                    class="form-control @error('actualUsage') is-invalid @enderror"
                                                    id="actualUsage"
                                                    wire:model="actualUsage.{{ $selectedDeliveryItem['item_id'] ?? 'default' }}"
                                                    wire:keyup="checkActualUsage">
                                                @error('actualUsage.' . ($selectedDeliveryItem['item_id'] ?? 'default'))
                                                <p class="small text-danger">{{ $message }}</p>
                                                @enderror
                                                @if (session()->has('stock_error'))
                                                <p class="small text-danger">{{ session('stock_error') }}</p>
                                                @endif
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-dark"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                    wire:click="processDelivery">
                                    Process Delivery
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('confirm-revert-back', event => {
        const { itemId, inputName,entryId  } = event.detail;

        Swal.fire({
            title: 'Are you sure?',
            text: "This will revert the stock entry update.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, revert it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // use @this.call to run the Livewire method
                @this.call('revertBackStock', itemId, inputName,entryId );
            }
        });
    });
</script>
<script>
    // for the stock modal open and close
    window.addEventListener('open-stock-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('stockEntryModal'));
        myModal.show();
    });

     window.addEventListener('close-stock-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('stockEntryModal'));
        myModal.hide();
    });

    // for the delivery modal open and close
     window.addEventListener('open-delivery-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('delieveryProcessModal'));
         myModal.show();
     });

      window.addEventListener('close-delivery-modal', event => {
        let deliveryModal = bootstrap.Modal.getInstance(document.getElementById('delieveryProcessModal'));
        if (deliveryModal) {
            deliveryModal.hide();
        }
    });

     window.addEventListener('open-garment-delivery-modal',event=>{
        let myModal = new bootstrap.Modal(document.getElementById('delieveryGarmentProcessModal'));
         myModal.show();
     });

      window.addEventListener('close-delivery-modal', () => {
        const modal = document.getElementById('delieveryGarmentProcessModal');
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    });

   
</script>

@endpush