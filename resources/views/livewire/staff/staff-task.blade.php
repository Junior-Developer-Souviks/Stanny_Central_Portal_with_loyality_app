<div class="">
    <!-- Navbar -->
    <!-- End Navbar -->
    <div class="container-fluid py-2">
      <div class="row align-items-center my-sm-3">
        <div class="col-lg-6 col-md-6 text-start">
            <h4 class="text-uppercase font-weight-bold mb-0">List Task > {{$staff->name}}</h4>
        </div>
        <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
            <div class="nav-wrapper position-relative end text-end">
                <!-- Single Button -->
                <a class="btn btn-primary btn-sm" href="{{route('staff.task.add',$staff->id)}}" role="button" >
                    <i class="material-icons text-white" style="font-size: 15px;">add</i>
                    <span class="ms-1"> New Task</span>
                </a>
                <a class="btn btn-dark btn-sm" href="{{route('staff.index')}}" role="button" >
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1"> Back</span>
                </a>
            </div>
        </div>
      </div>
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
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Date
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Stores
                                            </th>
                                            
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Action
                                            </th>
                                            {{-- <th class="text-secondary opacity-7"></th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- @forelse ($staff as $key=> $member)
                                        <tr>
                                            <td>
                                              
                                                   {{ucwords($member->name)}}
                                                
                                            </td>
                                            <td>
                                               
                                                    {{ ucwords($member->designationDetails->name ?? 'N/A')  }}
                                                
                                            </td>
                                            <td>
                                                <span>Mobile:</span><strong>{{ $member->phone ?? 'N/A' }}</strong> <br>
                                                <span>WhatsApp:</span><strong>{{ $member->whatsapp_no ?? 'N/A' }}</strong> 
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input ms-auto" type="checkbox" wire:click="toggleStatus({{ $member->id }})" 
                                                    @if ($member->status)
                                                        checked
                                                    @endif>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                 <a href="{{route('staff.update',$member->id)}}" class="btn btn-outline-info btn-sm custom-btn-sm" data-toggle="tooltip" data-original-title="Edit Staff">
                                                    Edit
                                                </a>
                                                 <a href="{{route('staff.view',$member->id)}}" class="btn btn-outline-info btn-sm custom-btn-sm" data-toggle="tooltip" data-original-title="View Staff">
                                                    View
                                                </a>
                                                 <a href="{{route('staff.task',$member->id)}}" class="btn btn-outline-info btn-sm custom-btn-sm" data-toggle="tooltip" data-original-title="Staff Task">
                                                    Task
                                                </a> --}}
                                               {{-- <button wire:click="deleteProduct({{ $member->id }})" class="btn btn-outline-danger btn-sm custom-btn-sm">Delete</button> --}}
                                            {{-- </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <p class="text-xs text-secondary mb-0">No Staff found.</p>
                                            </td>
                                        </tr>
                                        @endforelse --}}
                                    </tbody>
                                </table>
                            </div>
                            {{-- <div class="mt-3">
                                <nav aria-label="Page navigation">
                                    {{ $staff->links('pagination::bootstrap-5') }}
                                </nav>
                            </div> --}}
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>


