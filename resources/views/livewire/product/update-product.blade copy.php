<div>
<div class="container-fluid px-2 px-md-4">
    <div class="card card-body">
        <div class="row gx-4 mb-2">
            <div class="col-auto my-auto">
                <div class="h-100">
                    <h5 class="mb-1">
                       Update Product
                    </h5>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                <div class="nav-wrapper position-relative end text-end">
                    <!-- Single Button -->
                    <a class="btn btn-dark btn-sm" href="javascript:history.back();" role="button" >
                        <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                        <span class="ms-1">Back</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card card-plain h-100">
            <div class="card-body p-3">
                <form wire:submit.prevent="update">
                    <div class="row">     
                      
                        <div class="mb-3 col-md-2">
                            <label class="form-label">Collection <span class="text-danger">*</span></label>
                            <select wire:model="collection" wire:change="GetCollection($event.target.value)" class="form-control form-control-sm border border-1 p-2">
                                <option value="" selected hidden>Select collection</option>
                                    @foreach($Collections as $items)
                                        <option value="{{ $items->id }}" {{$collection==$items->id?"selected":""}}>{{ ucwords($items->title) }}@if($items->short_code)({{$items->short_code}})@endif</option>
                                    @endforeach
                            </select>
                            @error('collection')
                                 <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>          
                        <!-- Category Dropdown -->
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select wire:model="category_id" class="form-control form-control-sm border border-1 p-2">
                                <option value="" selected hidden>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                 <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                
                        <!-- Sub-Category Dropdown (depends on selected Category) -->
                        {{-- <div class="mb-3 col-md-4">
                            <label class="form-label">Sub Category </label>
                            <select wire:model="sub_category_id" class="form-control form-control-sm border border-1 p-2">
                                <option value="" selected hidden>Select Sub Category</option>
                                @foreach($subCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}">{{ $subCategory->title }}</option>
                                @endforeach
                            </select>
                            @error('sub_category_id')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div> --}}
                
                        <!-- Product Name -->
                        <div class="mb-3 col-md-5">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" class="form-control form-control-sm border border-1 p-2" placeholder="Product Name" >
                            @error('name')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                
                        <!-- Product Code -->
                        <div class="mb-3 col-md-2">
                            <label class="form-label">Product Code <span class="text-danger">*</span></label>
                            <input wire:model="product_code" type="text" class="form-control form-control-sm border border-1 p-2" placeholder="Product Code">
                            @error('product_code')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>

                        <!-- Short Description -->
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Short Description</label>
                            <input wire:model="short_description" id="short_description" type="text" class="form-control form-control-sm border border-1 p-2">
                            @error('short_description')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                
                        <!-- Description -->
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Description</label>
                            <input wire:model="description" id="description" type="text" class="form-control form-control-sm border border-1 p-2">
                            @error('description')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                
                        <!-- GST Details -->
                        <div class="mb-3 col-md-2">
                            <label class="form-label">GST Details (%)</label>
                            <input wire:model="gst_details" type="text" class="form-control form-control-sm border border-1 p-2" placeholder="GST Percentage">
                            @error('gst_details')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                
                        <!-- Product Image -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Product Image</label>
                            <input wire:model="product_image" type="file" class="form-control form-control-sm border border-1 p-2">
                             <!-- Show Existing Image -->
                            @if ($existing_image)
                              <img src="{{ asset('storage/' . $existing_image) }}" alt="Product Image" class="img-thumbnail mt-2" width="100">
                           @endif
                            @error('product_image')
                                <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>

                         {{-- Fabrics --}}
                         <div class="mb-3 col-md-6">
                            <label class="form-label">Fabrics <span class="text-danger">*</span></label>
                            <select wire:model="selectedFabrics" id="multiple" class="form-control form-control-sm border border-1 p-2" multiple>
                                <option value="" hidden>Select Fabrics</option>
                                @foreach($fabrics as $fabric)
                                    <option value="{{ $fabric->id }}" 
                                            @if(in_array($fabric->id, $selectedFabrics)) selected @endif>
                                        {{ $fabric->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedFabrics')
                                <p class="text-danger inputerror">{{ $message }}</p>
                            @enderror
                        </div>
                
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>

</div>
<script src="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ClassicEditor
        .create(document.querySelector('#short_description'))
        .catch(error => {
            console.error(error);
        });
    ClassicEditor
        .create(document.querySelector('#description'))
        .catch(error => {
            console.error(error);
        });
        
       $('#multiple').select2({
        placeholder: "Select Fabrics",
        allowClear: true,
    });

    // Sync Select2 values with Livewire property on change
    $('#multiple').on('change', function () {
        @this.set('selectedFabrics', $(this).val());
    });

    // Set initial values in Select2 based on Livewire's selectedFabrics
    Livewire.on('reinitializeSelect2', () => {
        $('#multiple').val(@this.get('selectedFabrics')).trigger('change');
    });  
});

// $("#multiple").select2({
//           placeholder: "Select a fabric",
//           allowClear: true
//       });

</script>
</div>
