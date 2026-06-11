<div>
    <!-- <h4>Supplier Details</h4> -->
    <div class="container">
        <section class="admin__title">
            <h5> Supplier Details</h5>
        </section>
        <section>
            <ul class="breadcrumb_menu">
                <li><a href="{{ route('suppliers.index') }}">Supplier List</a></li>
                <li>Supplier Details</li>
                <li class="back-button">
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0" href="{{ route('suppliers.index') }}" role="button">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1">Back</span>
                </a>
                </li>
            </ul>
        </section>
        <!-- Content -->
        <div class="flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card mb-4" style="height: 400px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Supplier Details</h5>
                                <h6 class="mb-1">
                                  <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-info custom-btn-sm">Edit</a>
                                </h6>
                            </div>
                            <div class="d-flex justify-content-start align-items-center mb-4">
                               
                                <div class="d-flex flex-column">
                                    <a href="">
                                        <h6 class="mb-0">{{ $supplier->name }}</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Contact info</h6>
                            </div>
                            <p class="mb-1"><i class="fas fa-envelope" style="font-size: 14px; color: #6c757d;"></i>
                            {{ $supplier->email }}
                            </p>
                            
                            <p class="mb-0"><i class="fas fa-phone" style="font-size: 14px; color: #6c757d;"></i>
                            {{$supplier->country_code_mobile .' '. $supplier->mobile }}
                            </p>
                            <p class="mb-0"> <i class="fab fa-whatsapp" style="font-size: 14px; color: #25D366;"></i>
                            {{ $supplier->is_wa_same ?$supplier->country_code_mobile .' '. $supplier->mobile : $supplier->country_code_whatsapp .' '.$supplier->whatsapp_no }}
                            </p>
                            <p class="mb-0"><i class="fas fa-phone-alt" style="font-size: 14px; color: #6c757d;"></i>
                                {{$supplier->country_code_alt_1 .' '. $supplier->alternative_phone_number_1 }}
                            </p>
                            <p class="mb-0"><i class="fas fa-phone-alt" style="font-size: 14px; color: #6c757d;"></i>
                                {{$supplier->country_code_alt_2 .' '. $supplier->alternative_phone_number_2 }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body p-3">
                        <h5 class="card-title">Billing Address</h5>
                            <p> {{ collect([$supplier->billing_address, $supplier->billing_landmark, $supplier->billing_city, $supplier->billing_state, $supplier->billing_country, $supplier->billing_pin])
                             ->filter() // Remove null or empty values
                            ->implode(', ') }} </p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Account Information</h5>
                            <!-- GST Certificate -->
                            @if($supplier->gst_number !=""||$supplier->credit_limit !=""||$supplier->credit_days !="")
                            @if ($supplier->gst_file)
                            <h6>GST Certificate:</h6>
                                <img src="{{ asset($this->existingGstFile) }}" alt="GST Certificate" class="img-thumbnail" width="200">
                            @else
                                <p>No GST certificate uploaded.</p>
                            @endif
                            @if($supplier->gst_number)
                            <p class="mb-1"><i class="fas fa-id-card" style="font-size: 14px; color: #6c757d;"></i>
                                {{$supplier->gst_number}}
                            </p>
                                @endif 
                                @if($supplier->credit_limit)
                            <p class="mb-1"><i class="fas fa-credit-card" style="font-size: 14px; color: #6c757d;"></i>
                                {{$supplier->credit_limit}}
                            </p>
                            @endif 
                            @if($supplier->credit_days)
                            <p class="mb-1"><i class="fas fa-calendar-day" style="font-size: 14px; color: #6c757d;"></i>
                                {{$supplier->credit_days}}
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
