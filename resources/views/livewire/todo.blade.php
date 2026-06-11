<div class="container">
    <section class="admin__title">
        <h5>ToDo Lists</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>ToDo Lists</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <!-- <p class="text-sm font-weight-bold">Items</p> -->
            </div>
        </div>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <label>Start Date</label>
                            <input type="date" class="form-control select-md bg-white search-input"
                                placeholder="Start Date" value="" wire:model="start_date">
                        </div>
                        <div class="col-auto mt-0">
                            <label>End Date</label>
                            <input type="date" class="form-control select-md bg-white search-input"
                                placeholder="End Date" value="" wire:model="end_date">
                        </div>
                        <div class="col-auto mt-0">
                            <label>Staff</label>
                            <select wire:model="search_staff_id" class="form-control ">
                                <option value="">Choose an Staff</option>
                                @foreach ($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ ucwords($staff->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto mt-5">
                            <button type="button" wire:click="filterData"
                                class="btn btn-outline-success select-md">Filter</button>
                            <button type="button" wire:click="resetSearch"
                                class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row mb-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                @if (session()->has('success'))
                                    <div class="alert alert-success" id="flashMessage">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session()->has('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                            <div class="row">

                            </div>
                        </div>
                        <div class="card-body pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                SL</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                User Name</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Customer</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Type</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Remarks</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                ToDo Date</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Created At</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($todolists as $k => $todo)
                                            <tr>
                                                <td>
                                                    <h6 class="mb-0 text-sm">{{ $todolists->firstItem() + $k }}</h6>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $todo->staff ?$todo->staff->name : "" }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $todo->customer ? $todo->customer->name : "" }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $todo->todo_type }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $todo->remark }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        @php
                                                            if (!empty($todo->todo_date)) {
                                                                echo date('d-m-Y', strtotime($todo->todo_date));
                                                            }

                                                        @endphp
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ date('d-m-Y', strtotime($todo->created_at)) }}</p>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-4">
                                    {{ $todolists->links() }}
                                </div>
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
                                <h5>Create ToDo List</h5>
                            </div>
                            <form wire:submit.prevent="submit">
                                <div class="row">
                                    <label class="form-label"> Customer</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:keyup="FindCustomer($event.target.value)"
                                            wire:model="customer" class="form-control"
                                            placeholder="Search customer by name, mobile, order ID">
                                        @if (!empty($searchResults))
                                            <div id="fetch_customer_details" class="dropdown-menu show w-100"
                                                style="max-height: 200px; overflow-y: auto;">
                                                @foreach ($searchResults as $customer)
                                                    <button class="dropdown-item" type="button"
                                                        wire:click="selectCustomer({{ $customer->id }})">
                                                        <img src="{{ $customer->profile_image ? asset($customer->profile_image) : asset('assets/img/user.png') }}"
                                                            alt="">
                                                        {{ ucfirst($customer->prefix . ' ' . $customer->name) }}
                                                        ({{ $customer->phone }})
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                        <input type="hidden" wire:model="customer_id" value="">

                                    </div>
                                    @error('customer_id')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <label class="form-label"> User <span class="text-danger">*</span></label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <select wire:model="staff_id"
                                            class="form-control @error('staff_id') is-invalid @enderror">
                                            <option value="">Choose an user</option>
                                            @foreach ($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ ucwords($staff->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('user_id')
                                        <p class='text-danger'>{{ $message }}</p>
                                    @enderror
                                    <label class="form-label"> Type <span class="text-danger">*</span></label>
                                    <div
                                        class="ms-md-auto pe-md-3 d-flex align-items-center mb-2  @error('todo_type') is-invalid @enderror">
                                        <select class="form-control" wire:model="todo_type">
                                            <option value="">Select ToDo Type</option>
                                            <option value="Order">Order</option>
                                            <option value="Payment">Payment</option>
                                            <option value="Delivery">Delivery</option>
                                            <option value="Delivery Return">Delivery Return</option>
                                            <option value="Cheque Deposit">Cheque Deposit</option>
                                        </select>
                                    </div>
                                    @error('todo_type')
                                        <p class='text-danger'>{{ $message }}</p>
                                    @enderror
                                    <label class="form-label"> ToDo Date<span class="text-danger">*</span></label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="date"
                                            class="form-control  @error('todo_date') is-invalid @enderror"
                                            wire:model="todo_date" placeholder="ToDo Date">
                                    </div>
                                    @error('todo_date')
                                        <p class='text-danger'>{{ $message }}</p>
                                    @enderror
                                    <label class="form-label"> Remarks <span class="text-danger">*</span></label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <textarea class="form-control @error('remark') is-invalid @enderror" wire:model="remark" placeholder="Remarks"></textarea>
                                    </div>
                                    @error('remark')
                                        <p class='text-danger'>{{ $message }}</p>
                                    @enderror
                                    <div class="mb-2 text-end mt-4">
                                        <button type="submit" class="btn btn-sm btn-success select-md"
                                            wire:loading.attr="disabled">
                                            <span>Create</span>
                                        </button>
                                    </div>
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
