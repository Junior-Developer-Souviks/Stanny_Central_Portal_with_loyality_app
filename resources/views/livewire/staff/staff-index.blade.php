<div class="">
    <!-- Navbar -->
    <!-- End Navbar -->
    <div class="container-fluid py-2">
        <section class="admin__title">
            <h5>Staff</h5>
        </section>

        {{-- search filter --}}
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="col-md-auto mt-3">
                        <a class="btn btn-outline-success select-md" href="{{route('staff.add')}}" role="button">
                            {{-- <i class="material-icons text-white" style="font-size: 15px;">add</i> --}}
                            <span class="ms-1">Add New Staff</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{count($staff_data)}} Staffs</p>
                </div>
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input"
                                id="customer" placeholder="Search by customer detail or Order number" value=""
                                style="width: 350px;" wire:keyup="FindCustomer($event.target.value)">
                        </div>
                        <div class="col-auto mt-0">
                            <select wire:model="branch_name" class="form-control select-md bg-white"
                                wire:change="SelectBranch($event.target.value)">
                                <option value="" hidden="" selected="">Branch</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto mt-0">
                            <select wire:model="designation_name" class="form-control select-md bg-white"
                                wire:change="SelectDesignation($event.target.value)">
                                <option value="" hidden="" selected="">Designation</option>
                                @foreach($designationList as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto mt-3">
                            <button type="button" wire:click="resetForm"
                                class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
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
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Branch</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Name</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Designation</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Team Lead</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Email</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Mobile</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                            Action</th>
                                        {{-- <th class="text-secondary opacity-7"></th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($staff_data as $key=> $member)
                                    <tr>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{$member->branch ? $member->branch->name : ""}}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{$member->prefix. "
                                                ".strtoupper($member->name.' '.$member->surname)}}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{
                                                ucwords($member->designationDetails->name ?? 'N/A') }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $member->teamLead ? $member->teamLead->name . ' ' . $member->teamLead->surname : 'N/A' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{
                                                ucwords($member->email ?? 'N/A') }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><strong> {{
                                                    $member->country_code_phone.' '.$member->phone ?? 'N/A' }}</strong>
                                              
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input ms-auto" type="checkbox"
                                                    wire:click="toggleStatus({{ $member->id }})" @if ($member->status)
                                                checked
                                                @endif>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{route('staff.update',$member->id)}}"
                                                class="btn btn-outline-primary select-md btn_action btn_outline"
                                                data-toggle="tooltip" data-original-title="Edit Staff" title="Edit">Edit
                                            </a>
                                            <a href="{{route('staff.view',$member->id)}}"
                                                class="btn btn-outline-primary select-md btn_action btn_outline"
                                                data-toggle="tooltip" data-original-title="View Staff" title="View">View
                                            </a>
                                            {{-- <a href="{{route('staff.task',$member->id)}}"
                                                class="btn btn-outline-info btn-sm custom-btn-sm mb-0"
                                                data-toggle="tooltip" data-original-title="Staff Task" title="Task">
                                                <span class="material-icons">assignment</span>
                                            </a> --}}
                                            {{-- @if ($member->designationDetails && $member->designationDetails->id ==
                                            2)
                                            <a href="{{route('staff.cities.add',$member->id)}}"
                                                class="btn btn-outline-primary select-md btn_action btn_outline"
                                                data-toggle="tooltip" data-original-title="Staff City" title="City">
                                                <span class="material-icons">place</span>
                                            </a>
                                            @endif --}}
                                            @if ($member->designationDetails)
                                            <a href="{{route('salesman.index',['staff_id'=>$member->id])}}"
                                                class="btn btn-outline-primary select-md btn_action btn_outline">
                                                Bill Books
                                            </a>
                                            @endif

                                            {{-- <button wire:click="deleteProduct({{ $member->id }})"
                                                class="btn btn-outline-danger btn-sm custom-btn-sm mb-0">Delete</button>
                                            --}}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">No Staff found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <nav aria-label="Page navigation">
                                {{ $staff_data->links() }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>