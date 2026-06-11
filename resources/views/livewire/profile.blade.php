    <div class="">
        <!-- Navbar -->
        <!-- End Navbar -->
        <div class="container-fluid px-2 px-md-4">
            <div class="page-header min-height-300 border-radius-xl mt-4"
                style="background-image: url('{{asset('assets/img/logos/profile.avif')}}');">
                <span class="mask  bg-gradient-primary  opacity-6"></span>
            </div>
            <div class="card card-body mx-3 mx-md-4 mt-n6">
                <div class="row gx-4 mb-2">
                    <div class="col-auto">
                        <div class="avatar avatar-xl position-relative">
                            <img src="{{asset('assets/img/profile_image.png')}}" alt="profile_image"
                                class="w-100 border-radius-lg shadow-sm">
                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                               {{$user->name}}
                            </h5>
                            <p class="mb-0 font-weight-normal text-sm">
                                {{$user->designationDetails->name}}
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                        <div class="nav-wrapper position-relative end-0">
                            <!-- <ul class="nav nav-pills nav-fill p-1" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 active " data-bs-toggle="tab" href="javascript:;"
                                        role="tab" aria-selected="true">
                                        <i class="material-icons text-lg position-relative">home</i>
                                        <span class="ms-1">App</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 " data-bs-toggle="tab" href="javascript:;"
                                        role="tab" aria-selected="false">
                                        <i class="material-icons text-lg position-relative">settings</i>
                                        <span class="ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul> -->
                            <div class="d-flex justify-start gap-4 mb-4">
                                <button
                                        class="btn btn-outline-denger select-md btn_outline {{ $activeTab === 'app' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                        wire:click="setActiveTab('app')"
                                    >
                                        Information
                                    </button>
                                    <button
                                        class="btn btn-outline-success select-md btn_outline {{ $activeTab === 'settings' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                        wire:click="setActiveTab('settings')"
                                    >
                                        Change Password
                                    </button>
                                    

                                </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="row">
                        <div class="col-12 col-xl-6">
                        @if ($activeTab === 'app')
                            <div class="card card-plain h-100">
                                <div class="card-header pb-0 p-3">
                                    <div class="row">
                                        <div class="col-md-8 d-flex align-items-center">
                                            <h6 class="mb-0">Profile Information</h6>
                                        </div>
                                        <!-- <div class="col-md-4 text-end">
                                            <a href="javascript:;">
                                                <i class="fas fa-user-edit text-secondary text-sm"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Edit Profile"></i>
                                            </a>
                                        </div> -->
                                    </div>
                                </div>
                                <div class="card-body p-3"> 
                                    <ul class="list-group">
                                        <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong
                                                class="text-dark">Full Name:</strong> &nbsp; {{$user->name}}</li>
                                        <li class="list-group-item border-0 ps-0 text-sm"><strong
                                                class="text-dark">Mobile:</strong> &nbsp; {{$user->phone}}</li>
                                        <li class="list-group-item border-0 ps-0 text-sm"><strong
                                                class="text-dark">Email:</strong> &nbsp; {{$user->email}}</li>
                                        <li class="list-group-item border-0 ps-0 text-sm"><strong
                                                class="text-dark">Location:</strong> &nbsp; {{ implode(', ', array_filter([
                                                        $user->billingAddressLatest->address ?? '',
                                                        $user->billingAddressLatest->landmark ?? '',
                                                        $user->billingAddressLatest->city ?? '',
                                                        $user->billingAddressLatest->state ?? '',
                                                        $user->billingAddressLatest->country ?? '',
                                                        $user->billingAddressLatest->zip_code ?? ''
                                                    ])) }}
                                        </li>
                                       
                                    </ul>
                                </div>
                            </div>
                        @elseif ($activeTab === 'settings')
                        <div class="row">
                            <div class="row">
                                <!-- <div class="col-12"> -->
                                @if (session()->has('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <form wire:submit.prevent="changePassword">
                                    <div class="mb-3 col-12">
                                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                        <input type="password" wire:model="new_password" id="new_password" class="form-control" placeholder="Enter New Password">
                                        @error('new_password')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 col-12">
                                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" wire:model="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter New Password">
                                        @error('confirm_password')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-success">Change Password</button>
                                </form>

                                <!-- </div> -->
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

