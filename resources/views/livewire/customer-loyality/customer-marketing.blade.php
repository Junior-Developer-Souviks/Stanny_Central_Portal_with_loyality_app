<div class="container">

    <section class="admin__title">
        <h5>Marketing Types</h5>
    </section>


    <section>

        <ul class="breadcrumb_menu">
            <li>Marketing Types</li>
            <li></li>
        </ul>

    </section>



    <section>

        <div class="search__filter">

            <div class="row align-items-center">

                <div class="col-auto">

                </div>

            </div>

        </div>

    </section>



    <div class="row mb-4">


        <!-- LEFT TABLE -->

        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">


            <div class="card my-4">


                <div class="card-header pb-0">


                    @if(session()->has('message'))

                    <div class="alert alert-success" id="flashMessage">

                        {{ session('message') }}

                    </div>

                    @endif


                </div>



                <div class="card-body pb-2">


                    <div class="table-responsive p-0">


                        <table class="table align-items-center mb-0">


                            <thead>

                                <tr>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bonder opacity-10">
                                        SL
                                    </th>


                                    <th class="text-uppercase text-secondary text-xxs font-weight-bonder opacity-10">
                                        Name
                                    </th>


                                    <th class="text-uppercase text-secondary text-xxs font-weight-bonder opacity-10">
                                        Status
                                    </th>


                                    <th class="text-uppercase text-secondary text-xxs font-weight-bonder opacity-10">
                                        Actions
                                    </th>


                                </tr>


                            </thead>



                            <tbody>



                            @forelse($marketingTypes as $k=>$item)


                                <tr>


                                    <td>

                                        <h6 class="mb-0 text-sm">
                                            {{$k+1}}
                                        </h6>

                                    </td>



                                    <td>

                                        <p class="text-xs font-weight-bold mb-0">

                                            {{ucwords($item->name)}}

                                        </p>


                                    </td>



                                    <td class="align-middle text-sm text-center">


                                        <div class="form-check form-switch">


                                            <input 
                                            class="form-check-input ms-auto"
                                            type="checkbox"
                                            wire:click="toggleStatus({{$item->id}})"
                                            @if($item->status) checked @endif
                                            >


                                        </div>


                                    </td>




                                    <td class="align-middle px-4">


                                        <button
                                        wire:click="edit({{$item->id}})"
                                        class="btn btn-outline-primary select-md btn_action btn_outline">

                                            Edit

                                        </button>



                                       <button
                                        wire:click="confirmDelete({{$item->id}})"
                                        class="btn btn-outline-danger select-md btn_outline">
                                        
                                            Delete
                                        
                                        </button>



                                    </td>



                                </tr>



                            @empty


                                <tr>

                                    <td colspan="4" class="text-center">

                                        <p class="text-xs text-secondary mb-0">

                                            No marketing type found.

                                        </p>


                                    </td>


                                </tr>


                            @endforelse




                            </tbody>



                        </table>



                    </div>


                </div>



            </div>



        </div>







        <!-- RIGHT FORM -->


        <div class="col-lg-4 col-md-6 mb-md-0 mb-4">


            <div class="card my-4">


                <div class="card-body px-0 pb-2 mx-4">


                    <div class="d-flex justify-content-between mb-3">


                        <h5>

                            {{$marketing_id ? "Update Marketing Type" : "Create Marketing Type"}}

                        </h5>


                    </div>





                    <form wire:submit.prevent="save">


                        <div class="row">



                            <label class="form-label">
                                Marketing Type
                            </label>


                            <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">


                                <input 
                                type="text"
                                wire:model="name"
                                class="form-control form-control-sm border border-2 p-2"
                                placeholder="Enter marketing type">


                            </div>



                            @error('name')

                                <p class="text-danger inputerror">

                                    {{$message}}

                                </p>

                            @enderror
                            

                            <div class="mb-2 text-end mt-4">


                                @if($marketing_id)

                                <a 
                                href="javascript:void(0)"
                                wire:click="resetForm"
                                class="btn btn-sm btn-danger select-md">

                                    Clear

                                </a>


                                @endif





                                <button 
                                type="submit"
                                class="btn btn-sm btn-success select-md"
                                wire:loading.attr="disabled">


                                    {{$marketing_id ? 'Update Type':'Create Type'}}


                                </button>


                            </div>



                        </div>



                    </form>



                </div>


            </div>



        </div>




    </div>



    <div class="loader-container" wire:loading>

        <div class="loader"></div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
    
    window.addEventListener('showDeleteConfirm', event => {
    
    
        let id = event.detail[0].id;
    
    
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
    
    
                @this.call('delete', id);
    
    
                Swal.fire(
    
                    "Deleted!",
    
                    "Marketing type deleted successfully.",
    
                    "success"
    
                );
    
            }
    
    
        });
    
    
    });
    
    
    </script>
</div>