
<div class="container-fluid px-2 px-md-4">
    <div class="card card-body">
        <div class="row gx-4 mb-2">
            {{-- <div class="col-auto">
                <div class="avatar avatar-xl position-relative">
                    <img src="{{ asset('assets') }}/img/bruce-mars.jpg" alt="profile_image"
                        class="w-100 border-radius-lg shadow-sm">
                </div>
            </div> --}}
            <div class="col-auto my-auto">
                <div class="h-100">
                    <h5 class="mb-1">
                       Create Product
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
        <form wire:submit='create'>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-plain h-100">
                        {{-- <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-md-8 d-flex align-items-center">
                                    <h6 class="mb-3">Profile Information</h6>
                                </div>
                            </div>
                        </div> --}}
                        <div class="card-body p-3">
                        
                                <div class="row">               
                                    <div class="mb-3 col-md-2">
                                        <label class="form-label">Collection <span class="text-danger">*</span></label>
                                        <select wire:model="collection" wire:change="GetCollection($event.target.value)" class="form-control form-control-sm border border-1 p-2">
                                            <option value="" selected hidden>Select collection</option>
                                                @foreach($Collections as $items)
                                                    <option value="{{ $items->id }}">{{ ucwords($items->title) }}@if($items->short_code)({{$items->short_code}})@endif</option>
                                                @endforeach
                                        </select>
                                        @error('collection')
                                            <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                    </div>
                                    <!-- Category Dropdown -->
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select wire:model="category_id"  class="form-control form-control-sm border border-1 p-2">
                                            <option value="" selected hidden>Select Category</option>
                                            @if($categories && count($categories))
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ ucwords($category->title) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('category_id')
                                            <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Product Name -->
                                    <div class="mb-3 col-md-4">
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
                                        <textarea wire:model="short_description" id="short_description" class="form-control form-control-sm border border-1 p-2"></textarea>
                                        @error('short_description')
                                            <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                    </div>
                            
                                    <!-- Description -->
                                    <div class="mb-3 col-md-12">
                                        <label class="form-label">Description</label>
                                        <textarea wire:model="description" id="description" class="form-control form-control-sm border border-1 p-2"></textarea>
                                        @error('description')
                                            <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                    </div>
                            
                                    <!-- GST Details -->
                                    <div class="mb-3 col-md-2">
                                        <label class="form-label">GST Details (%)</label>
                                        <input wire:model="gst_details" type="text" class="form-control form-control-sm border border-1 p-2" placeholder="GST Percentage" >
                                        @error('gst_details')
                                            <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                    </div>
                            
                                    <!-- Product Image -->
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Product Image</label>
                                        <input wire:model="product_image" type="file" class="form-control form-control-sm border border-1 p-2">
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
                                                <option value="{{ $fabric->id }}">{{ $fabric->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedFabrics')
                                            <p class="text-danger inputerror">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-plain mb-3">
                        <div class="card-body p-3">
                            <h6>Product Image</h6>
                            <div class="mb-2 mt-2">
                                @if ($product_image)
                                    <img src="{{ $product_image->temporaryUrl() }}" alt="Preview" width="100">
                                @endif
                                <input wire:model="product_image" type="file" class="form-control form-control-sm border border-1 p-2">
                            </div>
                            @error('product_image')<p class="text-danger inputerror">{{ $message }}</p>@enderror

                        
                        </div>
                    </div>
                    @if ($showAdditionalImageField)
                    <div class="card card-plain">
                        <div class="card-body p-3">
                            <h6>Additional Product Images</h6>
                            <div class="mb-2 mt-2">
                                <input type="file" wire:model="multipleImages" id="multipleImages" accept="image/*" multiple
                                    class="form-control border border-2 p-2 d-none" onchange="previewMultipleImages(event)">
                                <div id="multiple-image-preview" class="row">
                                    @foreach ($multipleImages as $key => $image)
                                        <div class="col-6 mb-3">
                                            <img src="{{ $image->temporaryUrl() }}" alt="Selected Image {{ $key + 1 }}"
                                                class="w-100 h-100 object-cover rounded-lg border border-gray-300">
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary mt-2"
                                    onclick="document.getElementById('multipleImages').click()">Upload Images</button>
                            </div>
                            @error('multipleImages.*')
                                <p class="text-danger inputerror">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-12 d-flex justify-content-start align-items-end" style="height: 100%;">
                <button type="submit" class="btn btn-dark">Submit</button>
            </div>
        </form>
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
        
        
});

$("#multiple").select2({
          placeholder: "Select a fabric",
          allowClear: true
      });

</script>

</div>

