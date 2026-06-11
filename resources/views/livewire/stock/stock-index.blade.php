<div class="container">
    <section class="admin__title">
        <h5>Stock Overview</h5>
    </section>

    <div class="d-flex justify-start gap-4 mb-4">
    <button
            class="btn btn-outline-denger select-md btn_outline {{ $activeTab === 'product' ? 'btn-primary' : 'btn-outline-secondary' }}"
            wire:click="setActiveTab('product')"
        >
            Product Stock
        </button>
        <button
            class="btn btn-outline-success select-md btn_outline {{ $activeTab === 'fabric' ? 'btn-primary' : 'btn-outline-secondary' }}"
            wire:click="setActiveTab('fabric')"
        >
            Fabric Stock
        </button>
    </div>

    <div>
        @if ($activeTab === 'product')
            <div class="card">
                <div class="card-body">
                    <!-- <button wire:click="exportStockProduct" class="btn btn-success btn-sm">
                        Export to Excel
                    </button> -->
                    <!-- Export & Date Filters -->
                    <!-- <div class="d-flex align-items-center gap-2 mb-3">
                        <input type="date" wire:model="startDate" class="form-control w-auto" />
                        <input type="date" wire:model="endDate" class="form-control w-auto" />
                        <button wire:click="exportStockProduct" class="btn btn-success btn-sm">
                            Export Product CSV
                        </button>
                    </div> -->


                    <div class="search__filter">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="" class="date_lable">Start Date</label>
                                        <input type="date" wire:model="startDateProduct" wire:change="AddStartDate($event.target.value)"
                                            class="form-control select-md bg-white" placeholder="Start Date">
                                    </div>
                                    <div class="col-auto">
                                        <label for="" class="date_lable">End date</label>
                                        <input type="date" wire:model="endDateProduct" wire:change="AddEndDate($event.target.value)"
                                            class="form-control select-md bg-white" placeholder="End Date">
                                    </div>
                                    <div class="col-auto mt-0 align-self-end">
                                        <input type="text" wire:model="searchProduct" class="form-control select-md bg-white" id="customer"
                                            placeholder="Search by product name" value="" style="width: 100px;"
                                            wire:keyup="FindCustomer($event.target.value)">
                                    </div>
                                    
                                    <div class="col-auto align-self-end">
                                        <button type="button" wire:click="resetForm"
                                            class="btn btn-outline-danger select-md mb-0">Clear</button>
                                    </div>
                                    <div class="col-auto align-self-end">
                                        <a href="javscript:void(0)" wire:click="exportStockProduct" class="btn btn-outline-success select-md mb-0"><i
                                                class="fas fa-file-csv me-1"></i>Export Product CSV</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                   

                    <h5 class="mb-3">Product Stock</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-items-center">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>GRN Quantity (Pieces)</th>
                                    <th>Order Quantity (Pieces)</th>
                                    <th>Piece Price</th>
                                    <th>Total Price</th>
                                    <th>Entry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $index => $product)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $product->product->name ?? 'N/A' }}</td>
                                        <td>{{ $product->qty_while_grn }}</td>
                                        <td>{{ $product->qty_in_pieces }}</td>
                                        <td>Rs. {{ number_format($product->piece_price, 2) }}</td>
                                        <td>Rs. {{ number_format($product->total_price, 2) }}</td>
                                        <td> {{ date('d-m-Y',strtotime($product->created_at))}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No product stock available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                         <!-- Pagination -->
                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($activeTab === 'fabric')
            <div class="card">
                <div class="card-body">
                <div class="search__filter">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto" style="margin-top: -27px;">
                                        <label for="" class="date_lable">Start Date</label>
                                        <input type="date" wire:model="startDateFabric" wire:change="AddStartDate($event.target.value)"
                                            class="form-control select-md bg-white" placeholder="Start Date">
                                    </div>
                                    <div class="col-auto" style="margin-top: -27px;">
                                        <label for="" class="date_lable">End date</label>
                                        <input type="date" wire:model="endDateFabric" wire:change="AddEndDate($event.target.value)"
                                            class="form-control select-md bg-white" placeholder="End Date">
                                    </div>
                                    <div class="col-auto mt-0">
                                        <input type="text" wire:model="searchFabric" class="form-control select-md bg-white" id="customer"
                                            placeholder="Search by product name" value="" style="width: 100px;"
                                            wire:keyup="FindCustomer($event.target.value)">
                                    </div>
                                    
                                    <div class="col-auto mt-3">
                                        <button type="button" wire:click="resetForm"
                                            class="btn btn-outline-danger select-md">Clear</button>
                                    </div>
                                    <div class="col-auto">
                                        <a href="javscript:void(0)" wire:click="exportStockFabric" class="btn btn-outline-success select-md"><i
                                                class="fas fa-file-csv me-1"></i>Export Fabric CSV</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Fabric Stock</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-items-center">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Fabric Name</th>
                                    <th>GRN Quantity (Meters)</th>
                                    <th>Order Quantity (Meters)</th>
                                    <th>Piece Price</th>
                                    <th>Total Price</th>
                                    <th>Entry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fabrics as $index => $fabric)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $fabric->fabric->title ?? 'N/A' }}</td>
                                        <td>{{ $fabric->qty_while_grn }}</td>
                                        <td>{{ $fabric->qty_in_meter }}</td>
                                        <td>Rs. {{ number_format($fabric->piece_price, 2) }}</td>
                                        <td>Rs. {{ number_format($fabric->total_price, 2) }}</td>
                                        <td> {{ date('d-m-Y', strtotime($fabric->created_at))}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No fabric stock available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{-- pagination --}}
                        <div class="mt-4">
                            {{$fabrics->links()}}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
