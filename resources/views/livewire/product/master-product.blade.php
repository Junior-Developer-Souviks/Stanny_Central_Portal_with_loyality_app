<div class="container">
    <section class="admin__title mb-5">
        <h5>Product List</h5>
    </section>
    <section>
        <div class="search__filter">
          
            <div class="row align-items-center justify-content-end">
                
                <div class="col-auto">
                    <div class="row g-3 align-items-center">

                        <div class="col-auto mt-0">
                            <select wire:model="searchFilter"  style="width: 100px;" wire:change="$refresh" class="form-control select-md bg-white">
                                <option value="" selected hidden>Search By</option>
                                @foreach($collection as $item)
                                    <option value="{{$item->id}}">{{ucwords($item->title)}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white" id="customer"
                                placeholder="Search Products" value=""
                                style="width: 200px;"  wire:keyup="FindProduct($event.target.value)">
                        </div>
                
                        <div class="col-auto mt-3">
                            <button type="button" wire:click="resetForm" class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-primary select-md" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-file-csv me-1"></i> Import
                            </button>
                        </div>
                      
                       
                        <div class="col-auto" >
                            <button wire:click="export" class="btn btn-outline-success select-md"><i class="fas fa-file-csv me-1"></i>Export</button>
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
                                            <div class="col-auto">
                                                <a wire:click="downloadProductCSV" class="btn btn-outline-success select-md"><i class="fas fa-file-csv me-1"></i>Sample CSV Download</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{route('product.add')}}" class="btn btn-outline-success select-md">Add Product</a>
                </div>
            </div>
        </div>
    </section> 

    <div class="row">
        <div class="col-12">
                <div class="card my-4">
                    <div class="card-body pb-0">
                        <!-- Display Success Message -->
                        @if (session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Display Error Message -->
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Image</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Collection</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Category</th>
                                    
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <span class="text-secondary text-xs font-weight-bold">
                                                @if ($product->product_image)
                                                    <img src="{{ asset('storage/'.$product->product_image) }}" alt="" style="width: 50px; height: 50px;">
                                                @else
                                                    <img src="{{asset('assets/img/placeholder.jfif')}}" alt="no-img" style="width: 50px; height: 50px;">    
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-secondary text-xs font-weight-bold">
                                                {{ $product->collection?$product->collection->title:""}}
                                            </p>
                                        </td> 
                                        <td><h6 class="mb-0 text-sm">{{ ucwords($product->name) }}</h6></td>
                                        <td><p class="text-xs font-weight-bold mb-0">{{ ucwords($product->category->title ?? 'N/A') }}</p></td>
                                        
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input ms-auto" type="checkbox" wire:click="toggleStatus({{ $product->id }})" 
                                                @if ($product->status)
                                                    checked
                                                @endif>
                                            </div>
                                        </td>
                                        <td class="align-middle action_tab">
                                            
                                            <a href="{{route('product.update',$product->id)}}" class="btn btn-outline-primary select-md btn_action btn_outline" data-toggle="tooltip">Edit</a>
                                            <a class="btn btn-outline-danger select-md btn_outline" wire:click="confirmDelete({{ $product->id }})" @click.stop>Delete</a>
                                            <!-- <a href="{{route('product.gallery',$product->id)}}" class="btn btn-outline-info btn-sm custom-btn-sm mb-0">Gallery </a> -->
                                            @if($product->collection_id==1)
                                                <a href="{{ route('measurements.index',$product->id) }}" class="btn btn-outline-primary select-md btn_action btn_outline" title="" data-toggle="tooltip">measurements
                                                @if(count($product->measurements)>0)
                                                    <span class="count">{{ $product->measurements->count() }}</span></a>
                                                @endif
                                                <a href="{{ route('product_fabrics.index',$product->id) }}" class="btn btn-outline-primary select-md btn_action btn_outline" title="" data-toggle="tooltip">Fabrics
                                                @if(count($product->fabrics)>0)
                                                    <span class="count">{{ $product->fabrics->count() }}</span></a>
                                                @endif
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <p class="text-xs text-secondary mb-0">No products found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end mt-2">
                                {{ $products->links() }}
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
                @this.call('deleteProduct', itemId); // Call Livewire method
                Swal.fire("Deleted!", "The product has been deleted.", "success");
            }
        });
    });
</script>

