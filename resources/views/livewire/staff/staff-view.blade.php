<div>
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center mb-1">
                        <!-- Active/Inactive Status -->
                        @if($staff->status == 1)
                        <span class="badge bg-success me-2 ms-2 rounded-pill">Active</span>
                        @else
                        <span class="badge bg-warning me-2 ms-2 rounded-pill">Inactive</span>
                        @endif
                    </div>
                    <h6 class="mt-1 mb-3">Staff Details</h6>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-2">
                    <a href="{{ route('staff.index') }}" class="btn btn-cta">
                        <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i> Back
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-start align-items-center mb-4">
                                @if($staff && $staff->image)
                                <img src="{{ asset('storage/' . $staff->image ?? 'dumy_user.png') }}" alt="Profile Image" class="rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                <img src="{{ asset('assets') }}/img/dumy_user.png" alt="Profile Image" class="rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <div class="d-flex flex-column">
                                    <h6 class="mb-0">{{ $staff->prefix ." ".$staff->name }}</h6>
                                    <p class="mb-1"><strong>Email:</strong> {{ ucwords($staff->branch->name ?? 'N/A') }}</p>
                                    <p class="mb-1"><strong>Branch:</strong> {{ ucwords($staff->branch->name ?? 'N/A') }}</p>
                                    <p class="mb-1"><strong>Designation:</strong> {{ ucwords($staff->designationDetails->name ?? 'N/A') }}</p>
                                </div>
                            </div>

                            {{-- <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Contact Info</h6>
                            </div> --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-1"> Contact Info</h6>
                                    <p class="mb-1"><i class="fas fa-phone" style="font-size: 14px; color: #6c757d;"></i> {{$staff->country_code_phone.' '. $staff->phone }}</p>
                                    <p class="mb-1"><i class="fab fa-whatsapp" style="font-size: 14px; color: #25D366;"></i> {{$staff->country_code_whatsapp.' '. $staff->whatsapp_no }}</p>
                                    <p class="mb-1"> <i class="fas fa-mobile-alt"></i> {{$staff->country_code_alt_1.' '. $staff->alternative_phone_number_1 }}</p>
                                    <p class="mb-1"> <i class="fas fa-mobile-alt"></i> {{$staff->country_code_alt_2.' '. $staff->alternative_phone_number_2 }}</p>
                                    @if (isset($staff->address) && ($staff->address->address || $staff->address->city))
                                      <p class="mb-1"><i class="fas fa-map-marker-alt" style="font-size: 14px; color: #6c757d;"></i> {{ $staff->address->address ?? 'N/A' }}, {{ $staff->address->city ?? 'N/A' }}</p>  
                                    @endif
                                    <p class="mb-1">  <i class="fas fa-calendar-alt"></i> {{ date('d-m-Y', strtotime($staff->dob)) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-1">Emergency Contact Info</h6>
                                    <p class="mb-1"><i class="fas fa-user-circle" style="font-size: 14px; color: #007bff;"></i> 
                                        <strong>Name:</strong> {{ $staff->emergency_contact_person ?? 'N/A' }}</p>
                                    
                                    <p class="mb-1"><i class="fas fa-phone-alt" style="font-size: 14px; color: #dc3545;"></i> 
                                        <strong>Contact:</strong> {{$staff->country_code_emergency_mobile.' '. $staff->emergency_mobile ?? 'N/A' }}</p>
                                    
                                    <p class="mb-1"><i class="fab fa-whatsapp" style="font-size: 14px; color: #25D366;"></i> 
                                        <strong>WhatsApp:</strong> {{$staff->country_code_emergency_whatsapp.' '. $staff->emergency_whatsapp ?? 'N/A' }}</p>
                    
                                    <p class="mb-1"><i class="fas fa-map-marker-alt" style="font-size: 14px; color: #6c757d;"></i> 
                                        <strong>Address:</strong> {{ $staff->emergency_address ?? 'N/A' }}</p>

                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Banking Details</h5>
                            <ul class="list-unstyled">
                                <li><strong>A/C Holder Name:</strong> {{ $staff->bank->account_holder_name ?? 'N/A' }}</li>
                                <li><strong>Bank Name:</strong> {{ $staff->bank->bank_name ?? 'N/A' }}</li>
                                <li><strong>Branch Name:</strong> {{ $staff->bank->branch_name ?? 'N/A' }}</li>
                                <li><strong>A/C No:</strong> {{ $staff->bank->bank_account_no ?? 'N/A' }}</li>
                                <li><strong>IFSC:</strong> {{ $staff->bank->ifsc ?? 'N/A' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">User ID Details</h5>
                            @if ($staff->user_id_back)
                                <h6>User ID Back:</h6>
                                <img src="{{ asset('storage/' . $staff->user_id_back) }}" alt="User ID Back" class="img-fluid border rounded mb-3">
                            @endif

                            @if ($staff->user_id_front)
                                <h6>User ID Front:</h6>
                                <img src="{{ asset('storage/' . $staff->user_id_front) }}" alt="User ID Front" class="img-fluid border rounded">
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Salary & Allowances</h5>
                            <ul class="list-unstyled">
                                <li><strong>Monthly Salary:</strong> Rs. {{ $staff->bank->monthly_salary ?? '0' }}</li>
                                <li><strong>Daily Salary:</strong> Rs. {{ $staff->bank->daily_salary ?? '0' }}</li>
                                <li><strong>Travel Allowance (Per KM):</strong> Rs. {{ $staff->bank->travelling_allowance ?? '0' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
