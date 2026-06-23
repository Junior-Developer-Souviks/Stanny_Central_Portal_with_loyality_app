<div class="container">


    <section class="admin__title">
        <h5>
            Customer Marketing Photos
        </h5>
    </section>


    <ul class="breadcrumb_menu">
        <li>
            Customer Photos
        </li>
    </ul>

    <div class="row mb-4">


        <!-- LEFT TABLE -->

        <div class="col-lg-8">


            <div class="card my-4">


                <div class="card-body">


                    @if(session()->has('message'))

                        <div class="alert alert-success">

                            {{ session('message') }}

                        </div>

                    @endif

                    <div class="table-responsive">


                        <table class="table align-items-center mb-0">


                            <thead>

                                <tr>

                                    <th>SL</th>
                                    <th>Photo</th>
                                    <th>Customer</th>
                                    <th>Marketing</th>
                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>


                                @foreach($photos as $key=>$photo)

                                    <tr>


                                        <td>
                                            {{ $key+1 }}
                                        </td>



                                        <td>

                                            <img
                                                src="{{ asset($photo->photo_path) }}"
                                                width="70"
                                                height="70">

                                        </td>

                                        <td>

                                            <h6 class="text-sm">

                                                {{ $photo->user->name ?? '' }}

                                            </h6>


                                            <span class="text-xs">

                                                {{ $photo->user->mobile ?? '' }}

                                            </span>


                                        </td>

                                        <td>

                                            
                                            @foreach($photo->marketingTypes as $type)

                                                <span class="badge bg-primary">

                                                    {{ $type->name }}

                                                </span>


                                            @endforeach


                                        </td>

                                        <td>


                                            <button
                                                wire:click="edit({{ $photo->id }})"
                                                class="btn btn-outline-primary select-md btn_action btn_outline">

                                                Edit

                                            </button>


                                        </td>


                                    </tr>


                                @endforeach


                            </tbody>


                        </table>


                    </div>

                    <div class="mt-3">

                        {{ $photos->links() }}

                    </div>

                </div>


            </div>


        </div>


        <!-- RIGHT FORM -->

        <div class="col-lg-4">


            <div class="card my-4">


                <div class="card-body mx-3">


                    <h5>

                        Assign Marketing 
                            Types
                        <a class="btn btn-outline-success select-md" href="{{ route('customer.marketing.list') }}">
                            Add 
                            
                        </a>
                    </h5>
          
                    @if($photoId)
  
                        <div class="mb-3">


                            <label>
                                Marketing Types
                            </label>



                            @foreach($marketingTypes as $type)


                                <div class="form-check">


                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        wire:model="selectedMarketing"
                                        value="{{ $type->id }}">

                                    <label>

                                        {{ $type->name }}

                                    </label>


                                </div>


                            @endforeach


                        </div>


                        <button
                            wire:click="update"
                            class="btn btn-success select-md">

                            Update

                        </button>

                        <button
                            wire:click="resetForm"
                            class="btn btn-danger select-md">

                            Clear

                        </button>

                    @else

                        <p class="text-muted">

                            Select photo to assign marketing type

                        </p>

                    @endif

                </div>


            </div>


        </div>

    </div>

</div>