<div class="container">
    <section class="admin__title">
        <h5>Business Type</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Business Type</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>
        <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <!-- <p class="text-sm font-weight-bold">Items</p> -->
                </div>
            </div>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input" id="customer"
                                placeholder="Search here" value=""
                                style="width: 350px;"  wire:keyup="FindBusiness($event.target.value)">
                        </div>
                        <div class="col-auto mt-3">
                            <button type="button" wire:click="resetForm" class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
                </div>
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
                                
                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">SL</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Title</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($business_types as $k => $businessType)
                                            <tr>
                                                <td><h6 class="mb-0 text-sm">{{ $k + 1 }}</h6></td>
                                                <td><p class="text-xs font-weight-bold mb-0">{{ $businessType->title }}</p></td>
                                                <td class="align-middle">
                                                    <button wire:click="edit({{ $businessType->id }})" class="btn btn-outline-primary select-md btn_action btn_outline" title="Edit">
                                                        Edit
                                                    </button>
                                                </td>
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
        
        <div class="col-lg-4 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-body px-0 pb-2 mx-4">
                            <div class="d-flex justify-content-between mb-3">
                                <h5>{{$businessTypeId ? "Update Business Type" : "Create Business Type"}}</h5>  
                            </div>
                            <form wire:submit.prevent="{{ $businessTypeId ? 'updateBusinessType' : 'storeBusinessType' }}">
                                <div class="row">
                                    <label class="form-label"> Title</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model="title" class="form-control border border-2 p-2" placeholder="Enter Title">
                                    </div>
                                    @error('title')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="mb-2 text-end mt-4">
                                        @if($businessTypeId)
                                            <a href="javascript:void(0);" 
                                            class="btn btn-sm btn-danger select-md" 
                                            wire:click.prevent="resetForm">
                                            Clear
                                        </a>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success select-md" wire:loading.attr="disabled">
                                            <span>{{ $businessTypeId ? 'Update' : 'Create' }}</span>
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
</div>