<div class="container">
    <section class="admin__title">
        <h5>Invoices</h5>
        <span class="badge bg-info" id="timeout-span"></span>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{count($invoices)}} Items</p>
                </div>
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white" id="customer"
                                placeholder="Search by invoice or order number" style="width: 350px;"
                                wire:keyup="FindCustomer($event.target.value)">
                        </div>
                        <div class="col-auto mt-0">
                            <select wire:model="created_by" class="form-control select-md bg-white"
                                wire:change="CollectedBy($event.target.value)">
                                <option value="" hidden="" selected="">Placed By</option>
                                @foreach($placed_by as $user)
                                @if(in_array($user->id, $usersWithOrders))
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
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
    </section>
    <div class="d-flex justify-start gap-4 mb-4">
        {{-- <button
            class="btn btn-outline-denger select-md btn_outline {{ $activeTab === 'normal' ? 'btn-primary' : 'btn-outline-secondary' }}"
            wire:click="setActiveTab('normal')">
            Normal
        </button> --}}
        {{-- <button
            class="btn btn-outline-success select-md btn_outline {{ $activeTab === 'manual' ? 'btn-primary' : 'btn-outline-secondary' }}"
            wire:click="setActiveTab('manual')">
            Manual
        </button> --}}


    </div>
    <div class="card my-2">
        <div class="card-header pb-0">
            <div class="row">
                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
            </div>

            <div class="table-responsive p-0">
                @if ($activeTab == 'normal')
                {{-- Normal invoice table here --}}
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <x-table-th>Date & Time</x-table-th>
                            <x-table-th>Invoice No</x-table-th>
                            <x-table-th>Order No</x-table-th>
                            <x-table-th>Customer</x-table-th>
                            <x-table-th>Products</x-table-th>
                            <x-table-th>Amount</x-table-th>
                            <x-table-th>Due Amount</x-table-th>
                            <x-table-th>Action</x-table-th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $key=> $item)
                        @php
                        $payment_status = 'Not Paid';
                        $payment_class = 'danger';
                        if($item->payment_status == 0){
                        $payment_status = 'Not Paid';
                        $payment_class = 'danger';
                        }else if($item->payment_status == 1){
                        $payment_status = 'Half Paid';
                        $payment_class = 'warning';
                        }else if($item->payment_status == 2){
                        $payment_status = 'Full Paid';
                        $payment_class = 'success';
                        }
                        @endphp
                        <tr>
                            <x-table-td>
                                <p class="small text-muted mb-1 badge bg-warning">
                                    {{-- Created At:- --}} {{date('d/m/Y h:i A', strtotime($item->created_at))}}
                                </p>
                                @if (!empty($item->updated_by))
                                <p class="small text-muted mb-1 badge bg-warning">
                                    {{-- Updated At:- --}} {{date('d/m/Y h:i A', strtotime($item->updated_at))}}
                                </p>
                                @endif
                            </x-table-td>
                            <x-table-td>{{"INV/2025/".$item->invoice_no}} </x-table-td>
                            <x-table-td>
                                <a href="{{ route('admin.order.view', $item->order_id) }}"
                                    class="btn btn-outline-secondary select-md btn_outline">{{$item->order ? $item->order->order_number : ""}}</a>
                            </x-table-td>
                            <x-table-td>
                                <p class="small text-muted mb-1">
                                    <span><strong>{{ucwords($item->customer?$item->customer->name:"")}}</strong> </span>
                                </p>
                            </x-table-td>
                            <x-table-td>
                                <button type="button" class="btn btn-outline-success select-md btn_outline"
                                    data-bs-toggle="modal" data-bs-target="#ViewProductModal{{$item->id}}"> View Items
                                    ({{count($item->order->items)}}) </button>
                                <div class="modal fade" id="ViewProductModal{{$item->id}}" tabindex="-1"
                                    aria-labelledby="ViewProductModalLabel{{$item->id}}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="ViewProductModalLabel{{$item->id}}">#
                                                    {{$item->invoice_no}} /
                                                    {{$item->customer?ucfirst($item->customer->name) : ""}}</h5>
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
                                                    <tbody id="modal-items">
                                                        @foreach ($item->order->items as $key => $product)
                                                        <tr>
                                                            <td>{{$key + 1}}</td>
                                                            <td>{{$product->product_name}}</td>
                                                            <td>{{$product->quantity}}</td>
                                                            <td>{{number_format($product->piece_price,2)}}</td>
                                                            <td>{{number_format($product->total_price,2)}}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-table-td>
                            <x-table-td>{{number_format($item->net_price,2)}} </x-table-td>
                            <x-table-td>{{number_format($item->due_amnt,2)}} </x-table-td>

                            <x-table-td>
                                {{-- <a href="#" class="btn btn-outline-success select-md btn_outline">Edit</a> --}}
                                <button wire:click="downloadOrderInvoice({{ $item->order_id }})"
                                    class="btn select-md btn-outline-success btn_outline">Download</button>
                                {{-- <a href="#" class="btn btn-outline-success select-md btn_outline">Download Slip</a>
                                --}}
                                {{-- <a href="#" class="btn select-md btn-outline-warning btn_outline"
                                    onclick="return confirm('Are you sure want to revoke?');">Revoke</a> --}}
                            </x-table-td>
                        </tr>

                        {{-- View Product Modal --}}
                        {{-- <tr>
                            <td colspan="7">

                            </td>
                        </tr> --}}
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>
                @elseif($activeTab == 'manual')
                {{-- Manual Invoice table here --}}
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <x-table-th>Date & Time</x-table-th>
                            <x-table-th>Invoice No</x-table-th>
                            <x-table-th>Order No</x-table-th>
                            <x-table-th>Customer</x-table-th>
                            <x-table-th>Products</x-table-th>
                            <x-table-th>Amount</x-table-th>
                            <x-table-th>Actions</x-table-th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($manualInvoices as $item)
                        <tr>
                            <x-table-td>
                                <p class="small text-muted mb-1 badge bg-warning">
                                    Created At:- {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y h:i A') }}
                                </p>
                            </x-table-td>
                            <x-table-td>{{ $item->invoice_no }}</x-table-td>
                            <x-table-td>{{ $item->reference }}</x-table-td>
                            <x-table-td>{{ ucwords($item->customer_name) }}</x-table-td>
                            <x-table-td>
                                <button type="button" class="btn btn-outline-success select-md btn_outline"
                                    data-bs-toggle="modal" data-bs-target="#ManualProductModal{{$item->id}}"> View Items
                                    ({{count($item->items)}}) </button>
                            </x-table-td>
                            <x-table-td>{{ number_format($item->total_amount,2) }}</x-table-td>
                            <x-table-td>
                                <button wire:click="downloadManualInvoice({{ $item->id }})"
                                    class="btn select-md btn-outline-success btn_outline">
                                    Download
                                </button>
                            </x-table-td>
                        </tr>
                        <tr>
                            <td colspan="7">
                                <div class="modal fade" id="ManualProductModal{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="ManualProductModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="ManualProductModalLabel{{ $item->id }}">
                                                    #{{ $item->invoice_no }} / {{ ucfirst($item->customer_name) }}
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
                                                            <td>{{ $product->product ? $product->product->name : "" }}
                                                            </td>
                                                            <td>{{ $product->quantity }}</td>
                                                            <td>{{ number_format($product->unit_price, 2) }}</td>
                                                            <td>{{ number_format($product->total, 2) }}</td>
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
                <div class="mt-4">
                    {{ $manualInvoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @if(empty($search))
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
    @endif
</div>
