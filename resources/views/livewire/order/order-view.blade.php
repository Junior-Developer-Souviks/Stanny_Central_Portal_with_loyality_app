<div class="container">
    <style>
        .skip-order {
            font-size: 24px !important;
            margin-top: 8px !important;
        }

        .catelog-wrap {
            margin-bottom: 25px;
        }

        .audio-stack {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .audio-stack audio {
            max-width: 214px;
        }

        p {
            font-size: 13px;
        }

        .td-details {
            padding: 15px 0;
        }

        .odd {
            border-bottom: 1px solid #ccc;
        }

        .odd:last-child {
            border-bottom: 0;
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
                <a href="{{route('admin.order.index')}}"
                    class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">Back </a>
            </li>

        </ul>
    </section>
    @if ($order->status == 'Cancelled')
    <div class="card shadow-sm mb-2">
        <div class="card-body text-center">
            <h3 class="text-danger skip-order">Order Skipped</h3>
            <p><strong>Reason:</strong> {{ $order->skip_order_reason ?? 'N/A' }}</p>
        </div>
    </div>
    @else
    <div id="export-area">
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
                                    <p class="small m-0">{{number_format($order->total_amount, 2)}}</p>
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
                                foreach ($orderItems as $item) {
                                if (!empty($item['deliveries']) && $item['deliveries']['status'] === 'Delivered') {
                                $hasDelivered = true;
                                break;
                                }
                                }
                            @endphp
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Customer Details</h6>
                                @if ($hasDelivered)
                                <p>
                                    <a class="btn btn-outline-success select-md"
                                        href="{{ route('orders.generatePdf', $order->id) }}"
                                        target="_blank">Download</a>
                                </p>
                                @endif
                            </div>
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
                           {{-- CLIENT IMAGE --}}
                            <div class="row mb-2">
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
                            <div class="row mb-2">
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
                            </div>
                            
                            {{-- VERIFIED VIDEO --}}
                            <div class="row mb-2">
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
                            <div class="row mb-2">
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
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mb-2">
            <div class="table-responsive">
                <div class="card-body">
                    <table class="table table-sm ledger">
                        <thead>
                            <tr>
                                <th class="" rowspan="1" colspan="1" style="width: 65px;" aria-label="price">Collection
                                </th>
                                <th class="w-50 " rowspan="1" colspan="1" style="width: 328px;" aria-label="products">
                                    Order
                                    Items</th>
                                <th class="" rowspan="1" colspan="1" style="width: 65px;" aria-label="price">price</th>
                                <th class="" rowspan="1" colspan="1" style="width: 50px;" aria-label="qty">
                                    qty</th>
                                <th class="" rowspan="1" colspan="1" style="width: 80px;" aria-label="total">total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($orderItems->isNotEmpty())
                            @foreach ($orderItems as $item)
                            <tr class="odd" style="background-color: #f2f2f2;">
                                <td>{{$item['collection_title']}}</td>
                                <td class="">
                                    <div class="d-flex justify-content-start align-items-center product-name">
                                       
                                        <div class="d-flex flex-column">
                                            <span
                                                class="text-nowrap text-heading fw-medium">{{$item['product_name']}}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span>{{number_format($item['price'], 2)}}</span></td>
                                <td><span>{{$item['quantity']}}</span></td>
                                <td><span>{{number_format($item['price']*$item['quantity'], 2)}}</span></td>
                            </tr>
                            {{-- Delivery Logs Section --}}
                            @if(!empty($item['deliveries']) && count($item['deliveries'])>0)
                            <tr>
                                <td colspan="5">
                                    <div class="col-12 mb-2 measurement_div" style="background: #fdfdfd !important;">
                                        <h6 class="badge bg-danger custom_success_badge">Delivery Logs</h6>
                                        <div class="row">
                                            <table class="table table-sm ledger">
                                                <thead>
                                                    <tr>
                                                      
                                                        <th rowspan="1" colspan="1" aria-label="products">Delivery
                                                            Date</th>
                                                       
                                                        <th rowspan="1" colspan="1" aria-label="products">
                                                            Status</th>
    
                                                        <th rowspan="1" colspan="1" aria-label="products">Delivered BY
                                                            ({{ isset($item['deliveries'][0]['delivered_by']) &&
                                                            $item['deliveries'][0]['delivered_by'] != 1 ? 'Production' :
                                                            'Admin' }})
                                                        </th> 
                                                       
                                                        <th class="" rowspan="1" colspan="1" aria-label="qty">
                                                            qty</th>
                                                        <th rowspan="1" colspan="1" aria-label="products">
                                                            Remarks</th>
                                                        <th class="" rowspan="1" colspan="1" aria-label="total">Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                   
                                                    @if ($item['deliveries'])
                                                    <tr class="odd" style="background-color: #f2f2f2;">
                                                        {{-- <td>{{ ++$index }}</td> --}}
                                                        <td>{{ date('d-m-Y h:i A ',timestamp:
                                                            strtotime($item['deliveries']['delivered_at'])) }}
                                                        </td>
                                                       
                                                        
                                                        <td>

                                                            @if($item['deliveries']['status']=='Pending')
                                                            <span class="badge bg-primary">Pending</span>
                                                            @endif
                                                            @if($item['deliveries']['status']=='Received by Sales Team')
                                                            <span class="badge bg-warning">Received by Sales Team</span>
                                                            @endif
                                                            @if($item['deliveries']['status']=='Rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                            @endif
                                                            @if($item['deliveries']['status']=='Alteration Required')
                                                            <span class="badge bg-info">Alteration Required</span>
                                                            @endif
                                                            @if($item['deliveries']['status']=='Delivered')
                                                            <span class="badge bg-success">Delivered</span>
                                                            @endif
                                                        </td>

                                                        <td>{{ $item['deliveries']['user']['name'] }}</td>

                                                        <td>
                                                            @if ($item['deliveries']['collection_id'] == 1)
                                                            {{ $item['deliveries']['fabric_quantity'] ?? '' }}
                                                            @else
                                                            {{ $item['deliveries']['delivered_quantity'] ?? '' }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button href="javascript:void(0)" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                class="btn btn-outline-info select-md mt-3"
                                                                title="{{ $item['deliveries']['remarks'] }}">Show
                                                                Remarks
                                                                Click Here</button>
                                                        </td>
                                                        <td>
                                                            @if($item['deliveries']['status']=='Pending')
                                                            <a wire:click="$dispatch('mark-as-received', {Id: {{ $item['deliveries']['id'] }}})"
                                                                class="btn btn-outline-warning select-md btn_outline"
                                                                data-toggle="tooltip">Receive by Sales Team</a>
                                                            @endif
                                                            @if($item['deliveries']['status']=='Received by Sales Team')
                                                            <a href="javascript:void(0)"
                                                                wire:click="$dispatch('delivered-to-customer', {orderId: '{{ $order->id }}',Id:{{ $item['deliveries']['id'] }} })"
                                                                class="btn btn-outline-success select-md btn_outline">Delivery
                                                                to Customer
                                                            </a>
                                                            @endif


                                                        </td>

                                                    </tr>
                                                    @endif
                                                    
                                                </tbody>
                                            </table>


                                        </div>
                                    </div>
                                </td>

                            </tr>
                            @endif
                            @if($item['collection_id']==1)
                            <tr>
                                <td colspan="6">
                                    <div class="td-details">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6 class="badge bg-danger custom_success_badge">Measurements</h6>
                                                <div class="row">

                                                    @foreach ($item['measurements'] as $m)
                                                    <div class="col-md-3 mb-2">
                                                        <label>
                                                            {{ $m['measurement_name'] }}
                                                            <strong style="display:block;">[{{
                                                                $m['measurement_title_prefix'] }}]</strong>
                                                        </label>
                                                        <input type="text"
                                                            class="form-control form-control-sm border border-1 text-center"
                                                            readonly value="{{ $m['measurement_value'] }}">
                                                            
                                                        @if(!empty($m['measurement_remarks']))
                                                            <textarea class="form-control form-control-sm bg-white text-muted mt-2" 
                                                                      rows="2" 
                                                                      readonly 
                                                                      style="resize: none; font-size: 0.8rem;">{{ $m['measurement_remarks'] }}</textarea>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                </div>


                                               @if(!empty($item['remarks']))
                                                <div class="mt-3">
                                                    <label for="remarks"><strong>Remarks:</strong></label>
                                                    <textarea class="form-control form-control-sm border border-1"
                                                        rows="3" disabled>{{$item['remarks']}}</textarea>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="col-lg-3">
                                                <p>FABRIC : <strong>{{$item['fabrics']->title ?? 'N/A'}}</strong></p>
                                                
                                                <p>CATLOGUE : <strong>{{
                                                       $item['catalogue'] ?? 'N/A'
                                                        }}</strong>
                                                    (PAGE:
                                                    <strong>{{$item['cat_page_number'] ?? 'N/A'}}</strong>
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
                                                <p>Fittings : <strong>{{$item['fittings'] ?? 'N/A'}}</strong></p>
                                                <p>Priority Level : <strong>{{$item['priority'] ?? 'N/A'}}</strong></p>
                                                @if(!empty($item['catlogue_images']))
                                                <div class="catelog-wrap">
                                                    <p>CATALOGUE IMAGES :</p>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($item['catlogue_images'] as $img)
                                                        <a target="_blank"
                                                            href="{{ asset('storage/'.$img->image_path) }}">
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
                                          
                                            <div class="col-lg-3">
                                              @if(in_array('mens_jacket_suit', $item['extra_type'] ?? []))
                                               <p><strong>Hand Stitching:</strong> {{ $item['mens_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                                <p><strong>Shoulder Type:</strong> {{ $item['shoulder_type'] ?? 'N/A' }}
                                                </p>
                                                <p><strong>Vents:</strong> {{ $item['vents'] ?? 'N/A' }}</p>
                                                @endif

                                                
                                                @if(in_array('ladies_jacket_suit', $item['extra_type'] ?? []))
                                                <p><strong>Hand Stitching:</strong> {{ $item['ladies_hand_stitching'] ?? 'N/A'
                                                    }}</p>
                                                <p><strong>Shoulder Type:</strong> {{ $item['shoulder_type'] ?? 'N/A'
                                                    }}</p>
                                                <p><strong>Vents Required:</strong> {{ $item['vents_required'] ?? 'N/A'
                                                    }}</p>
                                                <p><strong>Vents Count:</strong> {{ $item['vents_count'] ?? 'N/A' }}</p>
                                                @endif

                                                @if(in_array('trouser', $item['extra_type'] ?? []))
                                                <p><strong>Fold Cuff Required:</strong> {{ $item['fold_cuff_required']
                                                    ?? 'N/A' }}</p>
                                                <p><strong>Fold Cuff Size:</strong> {{ !empty($item['fold_cuff_size']) ? $item['fold_cuff_size']. ' cm' : 'N/A'
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

                                                    <p><strong>Client Name Required:</strong> {{ $item['client_name_required'] ??
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
                                    <td colspan="6">
                                        <div class="td-details">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="row">
                                                        @if(!empty($item['remarks']))
                                                                <div>
                                                                    <label for="remarks"><strong>Remarks:</strong></label>
                                                                   <div class="form-control form-control-sm border border-1 bg-light text-muted" 
                                                                      style="min-height: 60px; height: auto; white-space: pre-wrap; overflow: hidden;">
                                                                       {{$item['remarks']}}
                                                                    </div>
                                                                </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <p>Expected Delivery Date : <strong>{{$item['expected_delivery_date'] ??
                                                        'N/A'}}</strong></p>
                                                </div>
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
                            @if ($order->air_mail > 0)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-left"><small>Air Mail:</small></td>
                                <td><strong>{{ number_format(round($order->air_mail), 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-left"><small>Total Amount:</small></td>
                                <td><strong>{{number_format($order->total_amount, 2)}}</strong></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-left"><small>Paid Amount:</small></td>
                                <td><strong>{{number_format($order->paid_amount, 2)}}</strong></td>
                            </tr>
                            @if ($order->remaining_amount > 0)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-left"><small>Remaining Amount:</small></td>
                                <td><strong class="text-danger">{{number_format($order->remaining_amount, 2)}}</strong>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Content --}}

    <div wire:ignore.self class="modal fade" id="DeliveryModal" tabindex="-1" aria-labelledby="stockEntryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockEntryModalLabel">Delivery Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">



                    <div class="card">
                        <form wire:submit.prevent="deliveredToCustomerPartial">
                            <div class="card-body">
                                <h6>Delivery Status</h6>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">
                                            <strong>Status</strong> <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('status') is-invalid @enderror"
                                            wire:model="status">
                                            <option value="">Select Status</option>
                                            <option value="Delivered">Delivered</option>
                                            <option value="Alteration Required">Alteration Required</option>
                                            <option value="Reject">Rejected</option>

                                        </select>
                                        @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">
                                            <strong>Remarks</strong> <span class="text-danger">*</span>
                                        </label>

                                        <textarea class="form-control @error('remarks') is-invalid @enderror"
                                            wire:model="remarks">
                                        </textarea>
                                        @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-2 mt-4">
                                        <button class="btn btn-outline-success select-md">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    {{-- <button type="button" class="btn btn-primary">Save Stock</button> --}}
                </div>
            </div>
        </div>
    </div>
    @endif


</div>



@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Initialize all tooltips on the page
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
window.addEventListener('delivered-to-customer', event => {
        const { Id,orderId } = event.detail;
        let myModal = new bootstrap.Modal(document.getElementById('DeliveryModal'));
        myModal.show();
        Livewire.dispatch('openDeliveryModal', { Id, orderId });

    });
    window.addEventListener('close-delivery-modal', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('DeliveryModal'));
        if (modal) {
            modal.hide();
        }

        // ✅ Custom tracking logic here
        console.log("Delivery modal closed via Livewire event");

        // Optional: Reset modal content
        document.querySelector('#DeliveryModal form').reset();
        Swal.fire({
        title: "Success",
        text: "Customer Delivery Status updated successfully",
        icon: "success"
        }).then((result) => {
    if (result.isConfirmed) {
        // Reload the page
        window.location.reload();
    }
});
    });
    window.addEventListener('mark-as-received', event => {
    const {Id } = event.detail;
    Swal.fire({
            title: "Are you sure?",
            text: "The Production Team has marked this item as delivered. Please confirm that you have received it. Once confirmed, this action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, received by sales team!"
            }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('markReceivedConfirmed', {Id});
            }

            })

    });
</script>




@endpush