<div class="container">
    <section class="admin__title">
        <h5>Proforma List</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Proforma</li>
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
                            <input type="text" wire:model="search" wire:keyup="FindCustomer($event.target.value)"
                                class="form-control select-md bg-white" id="customer"
                                placeholder="Search by customer name or proforma number" value="" style="width: 350px;">
                        </div>
                        <div class="col-md-auto mt-3">
                            <a href="{{ route('admin.order.proformas.add') }}"
                                class="btn btn-outline-success select-md">Add Proforma</a>
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
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Date & Time
                                    </x-table-th>
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Proforma No
                                    </x-table-th>
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Customer
                                    </x-table-th>
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Products
                                    </x-table-th>
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Amount
                                    </x-table-th>
                                    <x-table-th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Action
                                    </x-table-th>
                                    <x-table-th class="text-secondary opacity-7"></x-table-th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice_data as $item)
                                <tr>
                                    <x-table-td>
                                        <p class="small text-muted mb-1 badge bg-warning">
                                            {{-- Created At:- --}} {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y
                                            h:i A') }}
                                        </p>
                                    </x-table-td>
                                    <x-table-td>{{ $item->proforma_number }}</x-table-td>
                                    <x-table-td style="word-break: break-word; white-space:normal;">{{ $item->customer ?
                                        ucwords($item->customer->name) : "" }}</x-table-td>

                                    <x-table-td>
                                        <button type="button" class="btn btn-outline-success select-md btn_outline"
                                            data-bs-toggle="modal" data-bs-target="#ProductModal{{$item->id}}"> View
                                            Items
                                            ({{count($item->items)}})
                                        </button>
                                    </x-table-td>
                                    <x-table-td>{{ number_format($item->total_amount,2) }}</x-table-td>
                                    <x-table-td>
                                        <button wire:click="downloadProformaInvoice({{ $item->id }})"
                                            class="btn select-md btn-outline-success btn_outline">
                                            Download
                                        </button>
                                    </x-table-td>
                                </tr>
                                {{-- product modal --}}
                                <tr>
                                    <td colspan="7">
                                        <div class="modal fade" id="ProductModal{{ $item->id }}" tabindex="-1"
                                            aria-labelledby="ProductModalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="ProductModalLabel{{ $item->id }}"
                                                            style="word-break: break-word; white-space:normal; max-width: 90%;">
                                                            #{{ $item->proforma_number }} / {{
                                                            ucwords($item->customer->name) }}
                                                        </h5>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Product</th>
                                                                    <th>Quantity</th>
                                                                    <th>Piece Price</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($item->items as $key => $product)
                                                                <tr>
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td>{{ $product->product ? $product->product->name :
                                                                        "" }}</td>
                                                                    <td>{{ $product->quantity }}</td>
                                                                    <td>{{ number_format($product->unit_price, 2) }}
                                                                    </td>
                                                                    <td>{{ number_format($product->total_price, 2) }}
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                        {{-- {{ $suppliers->links() }} --}}
                    </div>
                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            {{ $invoice_data->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>