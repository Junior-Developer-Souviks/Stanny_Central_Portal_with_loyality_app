

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Generate GRN</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-4 text-muted">PO: <span class="text-dark">{{ $purchaseOrder->unique_id }}</span></h6>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <label>
                    <input type="checkbox" wire:model="selectAll" id="bulkInCheckbox" wire:change="toggleAllCheckboxes">
                    Bulk In
                </label>
                <a href="{{route('purchase_order.index')}}" class="btn btn-cta" ><i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>Back</a>
            </div>
            @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif(Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ Session::get('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>  
            @endif
            <form wire:submit.prevent="generateGrn">
                  {{-- Card for Fabrics --}}
                @if($purchaseOrder->orderproducts->where('collection_id', 1)->isNotEmpty())
                <div class="card mt-2">
                    <div class="card-header">
                        <h6 class="mb-0">Fabric Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr class="text-center">
                                    <td>Bulk In</td>
                                    <th>Collection</th>
                                    <th>Fabric Name</th>
                                    <th>Order Quantity (in meters)</th>
                                    <th>GRN Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrder->orderproducts as $orderProduct)
                                    @if ($orderProduct->collection_id == 1)
                                        <tr class="text-center">
                                            <td>
                                                <input type="checkbox" wire:model="selectedFabricBulkIn" value="{{ $orderProduct->id }}" disabled>
                                            </td>
                                            <td>{{ $orderProduct->collection ? $orderProduct->collection->title : '' }}</td>
                                            <td>{{ $orderProduct->fabric ? $orderProduct->fabric->pseudo_name : 'N/A' }}</td>
                                            <td>{{ intval($orderProduct->qty_in_meter) }}</td>
                                            <td>
                                                <button type="button" wire:click="decrementGrnQuantity({{ $orderProduct->id }})">-</button>
                                                <input type="number"  wire:model="grnQuantities.{{ $orderProduct->id }}" value="{{ round($orderProduct->qty_in_meter) }}" min="0" disabled>
                                                <button type="button" wire:click="incrementGrnQuantity({{ $orderProduct->id }})">+</button>
                                            </td>
                                            <td>
                                                <input type="text" class="border-0 text-center bg-transparent" wire:model="prices.{{ $orderProduct->id }}" value="{{ $orderProduct->total_price }}" readonly>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                {{-- Card for Products --}}
                @if($purchaseOrder->orderproducts->where('collection_id', '!=', 1)->isNotEmpty())
                <div class="card mt-2">
                    <div class="card-header">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th>Bulk In</th>
                                    <th>Collection</th>
                                    <th>Product Name</th>
                                    <th>Order Quantity (in pieces)</th>
                                    <th>GRN Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrder->orderproducts as $orderProduct)
                                    @if ($orderProduct->collection_id != 1)
                                        @php 
                                            $rowCount = $orderProduct->qty_in_pieces; 
                                        @endphp
                                        @for ($i = 0; $i < $rowCount; $i++)
                                            <tr class="text-center">
                                                @if ($i === 0)
                                                    <td rowspan="{{ $rowCount }}">
                                                        <input type="checkbox" 
                                                               wire:model="selectedBulkIn" 
                                                               value="{{ $orderProduct->id }}" disabled>
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}">{{ $orderProduct->collection ? $orderProduct->collection->title : '' }}</td>
                                                    <td rowspan="{{ $rowCount }}">{{ $orderProduct->product ? $orderProduct->product->name : '' }}</td>
                                                    <td rowspan="{{ $rowCount }}">{{ $orderProduct->qty_in_pieces }}</td>
                                                    <td>
                                                        <button type="button" wire:click="decrementGrnQuantity({{ $orderProduct->id }})">-</button>
                                                        <input type="number"  wire:model="grnQuantities.{{ $orderProduct->id }}" value="{{ $orderProduct->qty_in_pieces }}" min="0" disabled>
                                                        <button type="button" wire:click="incrementGrnQuantity({{ $orderProduct->id }})">+</button>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="border-0 text-center bg-transparent" wire:model="prices.{{ $orderProduct->id }}" value="{{$orderProduct->total_price}}" readonly>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endfor
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit"  wire:click.prevent="generateGrn" class="btn btn-cta" @if (!$selectAll)
                        disabled
                    @endif>Generate GRN</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('bulkInCheckbox').addEventListener('change', function (event) {
            if (event.target.checked) {
                if (!confirm('Are you sure you want to bulk in?')) {
                    event.target.checked = false; // Revert checkbox if canceled
                    return;
                }
            }
            @this.call('toggleAllCheckboxes');
        });
        </script>
        
</div>

