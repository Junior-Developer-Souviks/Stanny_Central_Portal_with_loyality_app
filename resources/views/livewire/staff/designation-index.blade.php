<div class="container">
<section class="admin__title">
        <h5>Designation</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Designation</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>
        <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <!-- <p class="text-sm font-weight-bold">Items</p> -->
                </div>
            </div>
    </section>
    <div class="row mb-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                @if(session()->has('message'))
                                    <div class="alert alert-success" id="flashMessage">
                                        {{ session('message') }}
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                {{-- <div class="col-lg-6 col-5 my-auto text-end">
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                                        <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                            <input type="text" wire:model.debounce.500ms="search" class="form-control border border-2 p-2 custom-input-sm" placeholder="Enter Title">
                                            <button type="button" wire:target="search" class="btn btn-dark text-light mb-0 custom-input-sm">
                                                <span class="material-icons">search</span>
                                            </button>
                                        </div>
                                            <!-- Optionally, add a search icon button -->
                                        
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Name</th>
                                            {{-- <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Roles</th> --}}
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">No. of Staff</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($designations as $k => $designation)
                                        {{-- @dd($designation) --}}
                                            <tr>
                                                <td><h6 class="mb-0 text-sm">{{ucwords($designation->name)}}</h6></td>
                                                <td><p class="text-xs font-weight-bold mb-0">{{ $designation->users_count > 0 ? $designation->users_count : '0' }}</p></td>
                                                <td class="align-middle text-sm" style="text-align: center;">
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input ms-auto" 
                                                            type="checkbox" 
                                                            id="flexSwitchCheckDefault{{$designation->id}}" 
                                                            wire:click="toggleStatus({{$designation->id}})"
                                                            @if($designation->status) checked @endif
                                                        >
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center px-4"> 
                                                    <button wire:click="edit({{$designation->id}})" class="btn btn-outline-primary select-md btn_action btn_outline" title="Edit">Edit</button>
                                                    <a href="{{route('admin.staff.designation_wise_permission', $designation->id)}}" class="btn btn-outline-danger select-md btn_outline">Permissions</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- {{ $designations->links() }} --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-body px-0 pb-2 mx-4 asibe-bar">
                            <form wire:submit.prevent="storeOrUpdate">
                                <div class="row">
                                    <h5>{{ $designationId ? 'Update Designation' : 'Add New Designation' }}</h5>
                                    
                                    <label class="form-label">Name<span class="text-danger">*</span></label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model="name" class="form-control form-control-sm border border-2 p-2" placeholder="Enter Name" value="{{ucwords($name)}}">
                                    </div>
                                    @error('name')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    <!-- Roles Section -->
                                    {{-- <div class="mb-3">
                                        <label class="form-label">Roles</label>
                                        <div class="row">
                                            @foreach ($roleList as $role)
                                                <div class="col-md-6">
                                                    <div class="form-check ps-0 custom-checkbox">
                                                        <input class="form-check-input" type="checkbox" id="role{{$role->id}}" wire:model="roles" class="form-check-input" value="{{$role->id}}">
                                                        <i></i>
                                                        <label class="form-check-label" for="role{{$role->id}}">
                                                           {{$role->name}}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div> --}}
                                    <div class="mb-2 text-end">
                                        @if($designationId)
                                            <a href="javascript:void(0);" 
                                            class="btn btn-sm btn-danger select-md" 
                                            wire:click.prevent="resetForm">
                                            Clear
                                        </a>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success select-md" 
                                                wire:loading.attr="disabled">
                                            <span> 
                                                {{ $designationId ? 'Update Designation' : 'Create Designation' }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>