
<div class="container my-auto mt-5">
    <div class="row signin-margin">
        <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                        <div class="d-flex justify-content-center align-items-center">
                            <!-- Logo Section -->
                            <img src="{{ asset('assets') }}/img/logo.webp" alt="Logo" style="width: 100px; height: auto;" class="me-2">
                
                        </div>
                       
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="login"> 
                        @csrf
                        @if (Session::has('status'))
                        <div class="alert alert-success alert-dismissible text-white" role="alert">
                            <span class="text-sm">{{ Session::get('status') }}</span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                        <div class="input-group-outline mt-3">
                            <label class="form-label">Email</label>
                            <input wire:model.live='email' type="email" class="form-control">
                        </div>
                        @error('email')
                        <p class='text-danger inputerror'>{{ $message }} </p>
                        @enderror

                        <div class="input-group-outline mt-3">
                            <label class="form-label">Password</label>
                            <input wire:model.live="password" type="password" class="form-control">
                        </div>
                        @error('password')
                        <p class='text-danger inputerror'>{{ $message }} </p>
                        @enderror
                        <div class="text-center">
                            <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Sign
                                in</button>
                        </div>
                      
                    </form>
                </div>
            </div>
        </div>
    </div>
   
</div>