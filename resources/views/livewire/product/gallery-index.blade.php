<div class="container-fluid py-4">    
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
                                <div class="col-lg-6 col-7">
                                    <h5>{{$productName}} ->>Gallery</h5>
                                </div>
                            </div>
                        </div>
                            <div class="card-body px-0 pb-2">
                                <div class="d-flex flex-wrap">
                                    @foreach($galleries as $gallery)
                                        <div class="image-box position-relative mb-4" style="width: 15%; padding-right: 10px;">
                                            <img src="{{ asset($gallery->image) }}" alt="no-img" class="img-fluid">
                                            <!-- Cross button (delete) -->
                                            <button wire:click="destroy({{ $gallery->id }})" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1 btn-cross">
                                                <span class="material-icons">close</span>
                                            </button>
                                        </div>
                                    @endforeach
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
                                <h5>Create Gallery</h5>  
                            </div>
                            <form wire:submit.prevent="save">
                                <div class="row">
                                    <label class="form-label">Images</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="hidden" wire:model="id" id="product_id">
                                        <input type="file" wire:model="images" class="form-control border border-2 p-2" placeholder="Choose Images" multiple>
                                    </div>
                                    @error('images')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    @error('images.*') <p class="text-danger inputerror">{{ $message }}</p> @enderror
                                    <div class="mb-2 text-end">
                                        <a href="{{route('product.view')}}" class="btn btn-cta btn-sm mt-1">
                                         <i class="material-icons text-white" style="font-size: 18px;">chevron_left</i> 
                                            Back</a>
                                        <button type="submit" class="btn btn-cta btn-sm mt-1"> 
                                            Submit
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