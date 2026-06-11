<div class="container">
    <section class="admin__title">
        <h5>Supplier List</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Supplier</li>
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
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input" id="customer"
                                placeholder="Search by supplier name or PO number" value=""
                                style="width: 350px;"  wire:keyup="FindSupplier($event.target.value)">
                        </div>
                        <div class="col-md-auto mt-3">
                            <a href="{{ route('suppliers.add') }}" class="btn btn-outline-success select-md">Add Supplier</a>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
    </section>
    
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-body pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            @if (session()->has('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                           

                            @if (session()->has('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Name
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Email
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Phone
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                       Status
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                       Action
                                    </th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplier_data as $supplier)
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $supplier->prefix." ". $supplier->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $supplier->email ?? 'N/A'}}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{$supplier->country_code_mobile .' '. $supplier->mobile }}</p>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input ms-auto" type="checkbox" wire:click="toggleStatus({{ $supplier->id }})" 
                                                    @if ($supplier->status)
                                                        checked
                                                    @endif
                                                    >
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                            <a href="{{ route('suppliers.details', $supplier->id) }}" class="btn btn-outline-primary select-md btn_action btn_outline" data-toggle="tooltip" data-original-title="View Details" title="View Details">
                                             View
                                            </a>
                                            <a href="{{ route('suppliers.edit', $supplier->id) }}"  class="btn btn-outline-primary select-md btn_action btn_outline" data-toggle="tooltip" data-original-title="Edit supplier" title="Edit Supplier">
                                            Edit
                                            </a>
                                            <a class="btn btn-outline-danger select-md btn_outline" wire:click="confirmDelete({{ $supplier->id }})" @click.stop>Delete</a>
                                            </td>
                                        </tr>
                                @endforeach
                            </tbody>

                        </table>
                         {{-- {{ $suppliers->links() }} --}}
                    </div>
                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            {{ $supplier_data->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('showDeleteConfirm', function (event) {
        // console.log(event);
        let itemId = event.detail[0].itemId; // Assign itemId correctly
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
                @this.call('deleteSupplier', itemId); // Call Livewire method
                Swal.fire("Deleted!", "The supplier has been deleted.", "success");
            }
        });
    });
</script>