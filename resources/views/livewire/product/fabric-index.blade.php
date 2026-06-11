<div class="container">
    <section class="admin__title">
        <h5>Fabrics</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Fabrics</li>
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
                            
                        </div>
                        <div class="card-body pb-2">
                        <section>
                            <div class="search__filter">
                                <div class="row align-items-center justify-content-end">
                                    <div class="col-auto">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-auto">
                                               <select wire:model="fabric_category" wire:change="FabricCategoryFilter" id="fabric_category" class="form-control select-md bg-white mb-3">
                                                <option value="" selected hidden>Select Fabric</option>
                                                @foreach($fabricCategories as $item)
                                                    <option value="{{ $item->id }}">{{ ucwords($item->title) }}</option>
                                                @endforeach
                                            </select>    
                                            </div>
                                            <div class="col-auto">
                                                 <button type="button" wire:click="resetForm"
                                                 class="btn btn-outline-danger select-md"><i class="fas fa-sync-alt"></i></button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-outline-primary select-md" data-bs-toggle="modal" data-bs-target="#importModal">
                                                    <i class="fas fa-file-csv me-1"></i> Import
                                                </button>
                                            </div>
                                            <div class="col-auto">
                                                <button wire:click="export" class="btn btn-outline-success select-md"><i class="fas fa-file-csv me-1"></i>Export</button>
                                            </div>
                                            <div class="col-auto">
                                                <a href="{{ route('admin.fabrics.category') }}" class="btn btn-outline-success select-md">Fabric Category</a>
                                            </div>
                                            <!-- Import Modal -->
                                            <div wire:ignore.self class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="importModalLabel">Import CSV File</h5>
                                                            <button type="button" class="btn btn-outline-danger custom-btn-sm" data-bs-dismiss="modal">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Display Success/Error Messages -->
                                                            @if (session()->has('success'))
                                                                <div class="alert alert-success">{{ session('success') }}</div>
                                                            @endif
                                                            @if (session()->has('error'))
                                                                <div class="alert alert-danger">{{ session('error') }}</div>
                                                            @endif
                                                            
                                                            <form wire:submit.prevent="import" enctype="multipart/form-data">
                                                                <div class="mb-3">
                                                                    <label for="file" class="form-label">Upload CSV File</label>
                                                                    <input type="file" wire:model="file" class="form-control">
                                                                    @error('file') <span class="text-danger">{{ $message }}</span> @enderror
                                                                </div>
                                                            
                                                                <div class="d-flex justify-content-end  ">
                                                                    <div class="col-md-auto select-md">
                                                                        <button type="submit" class="btn-sm btn-success">
                                                                            <i class="fas fa-file-csv me-1"></i> Import
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                            </form>
                                                            <div class="col-auto">
                                                                <button wire:click="downloadFabricCSV" class="btn btn-outline-success select-md"><i class="fas fa-file-csv me-1"></i>Sample CSV Download</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                            <div class="d-flex justify-content-between mb-3">
                                <!-- Import Form -->
                                    @if(session()->has('error'))
                                        <span class="text-danger">{{ session('error') }}</span>
                                    @endif
                                    @if(session()->has('success'))
                                        <span class="text-success">{{ session('success') }}</span>
                                    @endif
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" >
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Image</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Style</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Radhey's Ref. No.</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Ref Number Company</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="">
                                        @forelse ($fabrics as $fabric)
                                        
                                            <tr data-id="{{ $fabric->id }}" class="handle">
                                                <td class="align-middle">
                                                     @if ($fabric->image)
                                                         <img src="{{ asset($fabric->image) }}" alt="Fabric Image" width="70" style="border-radius: 10px;">
                                                     @else
                                                         <img src="{{ asset('assets/img/fabric.webp') }}" alt="Fabric Image" width="70" style="border-radius: 10px;">
                                                     @endif
                                                </td>
                                                <td><h6 class="mb-0 text-sm">{{ ucwords($fabric->fabric_category ? $fabric->fabric_category->title : "") }}</h6></td>
                                                <td><h6 class="mb-0 text-sm">{{ ucwords($fabric->title) }}</h6></td>
                                                <td><h6 class="mb-0 text-sm">{{ ucwords($fabric->pseudo_name) }}</h6></td>
                                                <td class="align-middle text-center">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" 
                                                            class="form-check-input ms-auto" 
                                                            wire:click="toggleStatus({{ $fabric->id }})" 
                                                            @if ($fabric->status) checked @endif>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <button wire:click="edit({{ $fabric->id }})" class="btn btn-outline-primary select-md btn_action btn_outline">Edit
                                                    </button>
                                                    <a class="btn btn-outline-danger select-md btn_outline" wire:click="confirmDelete({{ $fabric->id }})" @click.stop>Delete</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No fabrics found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-end mt-2">
                                    {{$fabrics->links()}}
                                </div>
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
                                <h5>{{ $fabricId ? 'Update Fabric' : 'Create Fabric' }}</h5>
                            </div>
                            <form wire:submit.prevent="{{ $fabricId ? 'update' : 'store' }}">
                                {{-- Fabric Category --}}
                                <div class="form-group mb-3">
                                    <label for="category"> Style <span class="text-danger">*</span></label>
                                    <select wire:model="category" wire:change="loadLatestCategoryData" id="category" class="form-control" required>
                                        <option value="" selected hidden>-- Select Category --</option>
                                        @foreach ($fabricCategories as $item)
                                             <option value="{{ $item->id }}" {{ old('category') == $item->id ? 'selected' : '' }}>{{ ucwords($item->title) }}</option>
                                        @endforeach
                                    </select>
                                    @error('category') 
                                        <small id="categoryHelp" class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <!-- Fabric Title -->
                                <div class="form-group mb-3">
                                    <input type="hidden" wire:model="product_id" id="product_id">
                                    <label for="title"> Radhey's Ref. No. <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="title" 
                                        wire:model="title" 
                                        class="form-control border border-2 p-2" 
                                         placeholder="{{ $latestTitle ?? 'Enter Radhey\'s Ref. No.' }}" 
                                        aria-describedby="titleHelp">
                                    @error('title') 
                                        <small id="titleHelp" class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <!-- Fabric Pseudo Name -->
                                <div class="form-group mb-3">
                                    <input type="hidden" wire:model="product_id" id="product_id">
                                    <label for="pseudo_name"> Ref Number Company <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="pseudo_name" 
                                        wire:model="pseudo_name" 
                                        class="form-control border border-2 p-2" 
                                        placeholder="{{ $latestPseudoName ?? 'Enter Ref Number Company' }}" 
                                        aria-describedby="pseudoNameHelp">
                                    @error('pseudo_name') 
                                        <small id="pseudoHelp" class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                
                              <!--Threshold Price -->
                                <div class="form-group mb-3">
                                <label for="threshold_price">Threshold Price <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    id="threshold_price" 
                                    wire:model="threshold_price" 
                                    class="form-control border border-2 p-2" 
                                    placeholder="Enter Thereshold Price" 
                                    aria-describedby="thresholdpriceHelp">
                                @error('threshold_price') 
                                    <small id="thresholdpriceHelp" class="text-danger">{{ $message }}</small> 
                                @enderror
                            </div>
                            
                                <!--  Code -->
                                <div class="form-group mb-3">
                                    <label for="image">Fabric Image</label>
                                    <input 
                                        type="file" 
                                        id="image" 
                                        wire:model="image" 
                                        class="form-control border border-2 p-2" 
                                        aria-describedby="imageHelp">
                                             {{--  If new image is uploaded --}}
                                        @if(is_object($image))
                                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" width="100">
                                             {{-- If editing and existing image is available --}}
                                        @elseif($image)
                                            <img src="{{ asset($image) }}" alt="Existing Image" width="100">
                                        @endif
                                    @error('image') 
                                        <small id="imageHelp" class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
    
                                <!-- Submit Button -->
                                <div class="text-end">
                                @if($fabricId)
                                        <a href="javascript:void(0);" 
                                        class="btn btn-sm btn-danger select-md" 
                                        wire:click.prevent="resetFields">
                                        Clear
                                        </a>
                                        @endif
                                    <button type="submit" class="btn btn-sm btn-success select-md">
                                        {{ $fabricId ? 'Update Fabric' : 'Create Fabric' }}
                                    </button>
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
                Swal.fire("Deleted!", "The fabric has been deleted.", "success");
            }
        });
    });
</script>