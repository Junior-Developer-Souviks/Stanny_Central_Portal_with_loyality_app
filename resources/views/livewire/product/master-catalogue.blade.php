<div class="container">
    <section class="admin__title">
        <h5>Catalogue</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Catalogue</li>
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
        <!-- Catalog Table -->
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
                                @elseif(session()->has('error'))
                                    <div class="alert alert-danger" id="flashMessage">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                
                                <!-- <div class="col-lg-6 col-5 my-auto text-end">
                                    <div class="input-group w-100 search-input-group">
                                        <input type="text" wire:model.debounce.500ms="search" class="form-control border" placeholder="Search Title">
                                        <button type="button" wire:target="search" class="btn btn-outline-primary mb-0">
                                            <span class="material-icons">search</span>
                                        </button>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">SL</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Title</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Page Number</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($catalogues as $index => $catalogue)
                                            <tr>
                                                <td><h6 class="mb-0 text-sm">{{ $index + 1 }}</h6></td>
                                                
                                                <td><p class="text-xs font-weight-bold mb-0">{{ $catalogue->catalogueTitle ? $catalogue->catalogueTitle->title : "" }}</p></td>
                                                <td><p class="text-xs font-weight-bold mb-0">{{ $catalogue->page_number }}</p></td>
                                                <td class="align-middle">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input ms-auto" type="checkbox" wire:click="toggleStatus({{ $catalogue->id }})" @if($catalogue->status) checked @endif>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <button wire:click="edit({{ $catalogue->id }})" class="btn btn-outline-primary select-md btn_action btn_outline" title="Edit">
                                                        Edit
                                                    </button>
                                                    <a class="btn btn-outline-danger select-md btn_outline" wire:click="confirmDelete({{ $catalogue->id }})" @click.stop>Delete</a>
                                                    <a class="btn btn-outline-primary select-md btn_outline" href="{{route('product.catalogue.pages',$catalogue->id)}}">pages</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-nav">
                                {{-- {{ $catalogs->links() }} --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Form -->
        <div class="col-lg-4 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-body px-0 pb-2 mx-4">
                            <div class="d-flex justify-content-between mb-3">
                                <h5>{{ $catalogueId ? "Update Catalogue" : "Create Catalogue" }}</h5>  
                            </div>
                            <form wire:submit.prevent="{{ $catalogueId ? 'updateCatalogue' : 'storeCatalogue' }}">
                                <div class="row">
                                    <!-- Title -->
                                    <label class="form-label">Title</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <select wire:model="catalogue_title_id" class="form-control border border-2 p-2">
                                            <option value="" selected hidden>Select Title</option>
                                            @foreach ($catalogueTitle as $value)
                                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('catalogue_title_id')
                                        <p class="text-danger inputerror">{{ $message }}</p>
                                    @enderror

                                    <!-- Page Number -->
                                    <label class="form-label">Total Page Available</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="number" wire:model="page_number" class="form-control border border-2 p-2" placeholder="Enter Page Number">
                                    </div>
                                    @error('page_number')
                                        <p class="text-danger inputerror">{{ $message }}</p>
                                    @enderror

                                    <!-- Image -->
                                    <label class="form-label"> Pdf</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="file" wire:model="image" class="form-control border border-2 p-2">
                                    </div>
                                    <div>
                                    </div>
                                    @error('image')
                                        <p class="text-danger inputerror">{{ $message }}</p>
                                    @enderror

                                    <!-- Submit Button -->
                                    <div class="mb-2 text-end mt-4">
                                        @if($catalogueId)
                                        <a href="javascript:void(0);" 
                                        class="btn btn-sm btn-danger select-md" 
                                        wire:click.prevent="resetFields">
                                        Clear
                                        </a>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success select-md" wire:loading.attr="disabled">
                                            <span>{{ $catalogueId ? 'Update Catalogue' : 'Create Catalogue' }}</span>
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
                Swal.fire("Deleted!", "The catalogue has been deleted.", "success");
            }
        });
    });
</script>