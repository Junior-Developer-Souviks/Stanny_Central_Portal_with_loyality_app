<div class="container">
    <section class="admin__title">
        <h5>Fabric Categories</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Fabric Categories</li>
            <li></li>
        </ul>
        <div class="row align-items-center justify-content-between">
                <div class="col-auto">

                </div>
            </div>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        {{-- <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white" id="customer"
                                placeholder="Search here" value=""
                                style="width: 350px;"  wire:keyup="FindCategory($event.target.value)">
                        </div> --}}
                        {{-- <div class="col-auto mt-3">
                            <button type="button" wire:click="resetSearch" class="btn btn-outline-danger select-md">Clear</button>
                        </div> --}}
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
                               <div class="col d-flex justify-content-end">
                                    <a href="{{ route('admin.fabrics.index') }}" class="btn btn-outline-success select-md">Fabrics</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">SL</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Title</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categories as $k => $category)
                                            <tr>
                                                <td><h6 class="mb-0 text-sm">{{ $k + 1 }}</h6></td>
                                                <td><p class="text-xs font-weight-bold mb-0">{{ ucwords($category->title) ?? '' }}</p></td>
                                               <td class="align-middle text-sm text-center">
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input ms-auto" 
                                                            type="checkbox" 
                                                            id="flexSwitchCheckDefault{{ $category->id }}" 
                                                            wire:click="toggleStatus({{ $category->id }})"
                                                            @if($category->status) checked @endif
                                                        >
                                                    </div>
                                                </td>
                                                <td class="align-middle px-4">
                                                    <button wire:click="edit({{ $category->id }})" class="btn btn-outline-primary select-md btn_action btn_outline" title="Edit">Edit
                                                    </button>
                                                    <a class="btn btn-outline-danger select-md btn_outline" wire:click="confirmDelete({{ $category->id }})" @click.stop>Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                 <nav aria-label="Page navigation">
                                    {{ $categories->links() }}
                                 </nav>
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
                                <h5>{{$fabricCategoryId ? "Update Fabric Category" : "Create Fabric Category"}}</h5>  
                            </div>
                            <form wire:submit.prevent="{{ $fabricCategoryId ? 'update' : 'store' }}">
                                <div class="row">
    
                                    <label class="form-label"> Category Title</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model="title" class="form-control form-control-sm border border-2 p-2" placeholder="Enter Title">
                                    </div>
                                    @error('title')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror
    
                                  
                                    <div class="mb-2 text-end mt-4">
                                        @if($fabricCategoryId)
                                        <a href="javascript:void(0)" wire:click="resetFields" class="btn btn-sm btn-danger select-md">Clear</a>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success select-md" wire:loading.attr="disabled">
                                           <span>{{ $fabricCategoryId ? 'Update Category' : 'Create Category' }}</span>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('showDeleteConfirm', function (event) {
        // console.log(event);
        let itemId = event.detail[0].itemId; // Assign itemId correctly
        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('destroy', itemId); // Call Livewire method
                Swal.fire("Deleted!", "The category has been deleted.", "success");
            }
        });
    });
</script>