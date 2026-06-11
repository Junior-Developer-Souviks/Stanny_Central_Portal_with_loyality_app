<div class="container">

    <section class="admin__title">
        <h5>Create Product</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="javascript:history.back();">Product</a></li>
            <li>Create Product</li>
            <li class="back-button">
                <a href="javascript:history.back()"
                    class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">Back </a>
            </li>
        </ul>
    </section>

    <div class="card card-body">
        <form wire:submit='create'>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-plain h-100">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Collection <span class="text-danger">*</span></label>
                                    <select wire:model="collection" wire:change="GetCollection($event.target.value)"
                                        class="form-control form-control-sm border border-1 p-2">
                                        <option value="" selected hidden>Select collection</option>
                                        @foreach($Collections as $items)
                                        <option value="{{ $items->id }}">{{ ucwords($items->title)
                                            }}@if($items->short_code)({{$items->short_code}})@endif</option>
                                        @endforeach
                                    </select>
                                    @error('collection')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                </div>
                                <!-- Category Dropdown -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select wire:model="category_id"
                                        class="form-control form-control-sm border border-1 p-2">
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
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input wire:model="name" type="text"
                                        class="form-control form-control-sm border border-1 p-2"
                                        placeholder="Product Name">
                                    @error('name')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                </div>

                                <!-- Product Code -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                    <input wire:model="product_code" type="text"
                                        class="form-control form-control-sm border border-1 p-2"
                                        placeholder="Product Code">
                                    @error('product_code')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                </div>
                            </div>
                            @if($collection==1)
                            <div class="row">
                                <div class="mb-3">
                                    <h6 class="badge bg-danger custom_danger_badge">Product Fabrics</h6>
                                    <!-- Select All Checkbox -->
                                    <div class="form-check ps-0 custom-checkbox mb-2 selectBox">

                                        <label class="form-check-label text-uppercase text-sm" for="selectAllFabrics">
                                            <input class="form-check-input" type="checkbox" id="selectAllFabrics"
                                                wire:click="toggleSelectAll" wire:model="selectAll">
                                            <i></i> Select All
                                        </label>
                                    </div>
                                    <div class="row mt-2">
                                        @foreach($fabrics as $fabric)
                                        <div class="col-md-2">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input class="form-check-input" type="checkbox" id="role{{$fabric->id}}"
                                                    wire:model="selectedFabrics" class="form-check-input"
                                                    value="{{$fabric->id}}" style="position: absolute;">
                                                <i></i>
                                                <label class="form-check-label text-uppercase text-sm"
                                                    for="role{{$fabric->id}}">
                                                    {{$fabric->title}}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                        @error('selectedFabrics')
                                        <p class="text-danger inputerror">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-plain">
                        <div class="card-body p-3">
                            <label class="form-label">GST Details (%)</label>
                            <input wire:model="gst_details" type="text"
                                class="form-control form-control-sm border border-1 p-2" placeholder="GST Percentage">
                            @error('gst_details')
                            <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror

                            <!-- Product Image -->
                            <label class="form-label">Product Image</label>
                            <div class="mb-2 mt-2">
                                @if ($product_image)
                                <img src="{{ $product_image->temporaryUrl() }}" alt="Preview" width="100">
                                @endif
                                <input wire:model="product_image" type="file"
                                    class="form-control form-control-sm border border-1 p-2">
                            </div>
                            @error('product_image')<p class="text-danger inputerror">{{ $message }}</p>@enderror
                            @if ($showAdditionalImageField)
                            <label class="form-label">Additional Product Images</label>
                            <div class="mb-2 mt-2">
                                <input type="file" wire:model="multipleImages" id="multipleImages" accept="image/*"
                                    multiple class="form-control border border-2 p-2 d-none"
                                    onchange="previewMultipleImages(event)">
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
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-12 d-flex justify-content-start align-items-end" style="height: 100%;">
                <!-- <button type="submit" class="btn btn-dark">Submit</button> -->
                <button type="submit" class="btn btn-sm btn-success"><i class="material-icons text-white"
                        style="font-size: 15px;">add</i>Add</button>
            </div>

        </form>
        <div class="loader-container" wire:loading>
            <div class="loader"></div>
        </div>
    </div>


    <script type="text/javascript" src="{{ asset('assets/ckeditor/ckeditor/ckeditor.js') }}"></script>
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        window.addEventListener('ck_editor_load', function(event) { 
    // Handle short_desc_editor
    var shortDescTextArea = document.getElementById('short_description');
    if (shortDescTextArea) {
      // Check if CKEditor instance already exists and destroy it
      if (CKEDITOR.instances['short_description']) {
        CKEDITOR.instances['short_description'].destroy(true);
      }
      
      // Initialize CKEditor for short_desc_editor
      if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('short_description');

        // Sync CKEditor data to Livewire
        CKEDITOR.instances['short_description'].on('change', function() {
            @this.set('short_description', CKEDITOR.instances['short_description'].getData());
        });
      } else {
        console.error('CKEditor is not defined!');
      }
    }

    var DescTextArea = document.getElementById('description');
    if (DescTextArea) {
      // Check if CKEditor instance already exists and destroy it
      if (CKEDITOR.instances['description']) {
        CKEDITOR.instances['description'].destroy(true);
      }
      
      // Initialize CKEditor for short_desc_editor
      if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('description');

        // Sync CKEditor data to Livewire
        CKEDITOR.instances['description'].on('change', function() {
            @this.set('description', CKEDITOR.instances['description'].getData());
        });
      } else {
        console.error('CKEditor is not defined!');
      }
    }
  });

   $("#multiple").select2({
          placeholder: "Select a fabric",
          allowClear: true
      });

      

    </script>

</div>