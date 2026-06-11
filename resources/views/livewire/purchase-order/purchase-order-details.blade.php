<div class="container">
    <section class="admin__title">
        <h5>Purchase Order Detail</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Purchase Order</li>
            <li><a href="{{route('purchase_order.create')}}">PO</a></li>
            <li>Purchase Order Detail</li>
            <li class="back-button">
                <a href="{{ route('purchase_order.index') }}" class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">Back to PO </a>
            </li>
          </ul>
    </section>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-xs font-weight-bold mb-1">Order ID:</p>
                            <p><strong>{{$purchaseOrder->unique_id}}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-xs font-weight-bold mb-1">Supplier:</p>
                            <p>{{$purchaseOrder->supplier ? $purchaseOrder->supplier->name : ""}}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-xs font-weight-bold mb-1">Contact:</p>
                            <p>{{$purchaseOrder->supplier ? $purchaseOrder->supplier->mobile : ""}}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-xs font-weight-bold mb-1">Email:</p>
                            <p>{{$purchaseOrder->supplier ? $purchaseOrder->supplier->email : ""}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-start gap-4 mb-4">
            <button
                class="btn btn-sm px-4 py-2 {{ $activeTab === 'fabric' ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="setActiveTab('fabric')"
            >
                Fabric Details
            </button>
             <button
                class="btn btn-sm px-4 py-2 {{ $activeTab === 'product' ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="setActiveTab('product')"
            >
                Product Details
            </button>
        </div>
        @if($activeTab === 'product')
            <!-- Product Table -->
            <div class="col-md-12 ">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Products Details</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">#</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Product</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Quantity(Pieces)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">GRN Quantity(Pieces)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Piece Price</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    $totalProductPrice = 0;
                                @endphp
                                @if($purchaseOrder->orderproducts->where('stock_type', 'product')->count() > 0)
                                @foreach ($purchaseOrder->orderproducts as $index => $item)
                                {{-- @dd($item) --}}
                                    @if($item->stock_type === 'product')
                                        <tr>
                                            <td>{{$index + 1}}</td>
                                            <td>{{ ucwords($item->product_name) }}</td>
                                            <td>{{$item->qty_in_pieces }}</td>
                                            <td>{{ intval($item->qty_while_grn_product) }}</td>
                                            <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                                            <td>Rs. {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                        @php
                                            $totalProductPrice += $item->total_price;
                                        @endphp
                                    @endif
                                @endforeach
                                @else
                                    <tr>
                                        <td colspan="5">No products found</td>
                                    </tr>
                                @endif
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">Total PO Price</td>
                                        <td>Rs. <strong>{{ number_format($totalProductPrice, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'fabric')
             <!-- Fabric Table -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Fabric Details</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">#</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Fabric Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Quantity (meters)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">GRN Quantity (meters)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Unit Price</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalFabricPrice = 0;
                                    @endphp
                                    @foreach($purchaseOrder->orderproducts as $index => $item)
                                    @if($item->stock_type === 'fabric')
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->fabric ? $item->fabric->pseudo_name : '' }}</td>
                                            <td>{{ intval($item->qty_in_meter) }}</td>
                                            <td>{{ intval($item->qty_while_grn_fabric) }}</td>
                                            <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                                            <td>Rs. {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                        @php
                                            $totalFabricPrice += $item->total_price;
                                        @endphp
                                    @endif
                                    @endforeach
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">Total Fabric Price</td>
                                        <td>Rs. <strong>{{ number_format($totalFabricPrice, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
