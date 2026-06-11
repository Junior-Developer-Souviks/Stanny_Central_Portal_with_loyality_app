<div class="container">
    <section class="admin__title">
        <h5>Edit Purchase Order</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Purchase Order</li>
            <li><a href="{{route('purchase_order.create')}}">PO</a></li>
            <li>Edit Purchase Order</li>
            <li class="back-button">
                <a href="{{ route('purchase_order.index') }}" class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    Back to PO 
                </a>
            </li>
          </ul>
    </section>
    <form wire:submit.prevent="updatePurchaseOrder">
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0" style="width: 300px;">
                            <label for="suppliers" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select wire:model="selectedSupplier" wire:change="SelectedSupplier($event.target.value)" id="suppliers" class="form-control select-md bg-white">
                                <option value="" selected hidden>-- Select Supplier --</option>
                                @if (!empty($suppliers) && count($suppliers)>0)
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{$supplier->id}}" {{ $supplier->id == $selectedSupplier ? 'selected' : ""}}>{{$supplier->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('suppliers')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card h-100 mt-1">
            <div class="card-body p-3">
                <!-- Address Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Supplier Billing Address</h6>
                            @if ($selectedSupplier)
                            @php
                                $supplier = $suppliers->where('id',$selectedSupplier)->first();
                            @endphp
                                @if ($supplier)
                                <p>
                                    <strong>Address</strong>: {{$supplier->billing_address}}<br>
                                    <strong>Landmark</strong>: {{$supplier->billing_landmark ?? '';}}<br>
                                    <strong>City</strong>: {{$supplier->billing_city}}<br>
                                    <strong>State</strong>: {{$supplier->billing_state}}<br>
                                    <strong>Country</strong>: {{$supplier->billing_country}}<br>
                                    <strong>Pincode</strong>: {{$supplier->billing_pin}}
                                </p>
                                @endif
                            @endif
                        </div>
                    </div>
                <!-- Item Details -->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <h6 class="badge bg-danger custom_danger_badge">Item Details</h6>
                        </div>
                    <div class="col-md-12">
                        @foreach ($rows as $index =>$row)
                            <div class="row mb-3"> 
                                <div class="col-md-3">
                                    <label for="collections_{{ $index }}" class="form-label"><strong>Collection</strong> <span class="text-danger">*</span></label>
                                    <select wire:model="rows.{{ $index }}.collections" wire:change="SelectedCollection({{ $index }},$event.target.value)" id="collections_{{$index}}" class="form-control form-control-sm border border-1 p-2" >
                                        <option value="" selected hidden>Select Collection</option>
                                        @if (!empty($collections))
                                            @foreach ($collections as $collection)
                                                <option value="{{$collection['id']}}">{{$collection['title']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('rows.'.$index.'.collections')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if (!empty($row['fabrics']))
                                    <div class="col-md-3" wire:ignore>
                                        <label for="fabric_{{$index}}" class="form-label"><strong>Fabric</strong> <span class="text-danger">*</span></label>
                                        <select wire:model="rows.{{$index}}.fabric" id="fabric_{{$index}}" class="form-control form-control-sm border border-1 p-2 chosen-select"
                                        data-locked="{{ $row['fabric'] ? 'true' : 'false' }}">
                                            <option value="" selected hidden>Select Fabric</option>
                                            @foreach ($row['fabrics'] as $fabric)
                                                <option value="{{ $fabric['id'] }}">
                                                    {{ $fabric['title'] }} ({{ $fabric['pseudo_name'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('rows.'.$index.'.fabric')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @elseif(!empty($row['products']))
                                    <div class="col-md-3" wire:ignore>
                                        <label for="product_{{$index}}" class="form-label"><strong>Product</strong> <span class="text-danger">*</span></label>
                                        <select type="text" wire:model="rows.{{$index}}.product" id="product_{{$index}}" class="form-control form-control-sm border border-1 p-2 chosen-select" placeholder="Search product by name" data-locked="{{ $row['fabric'] ? 'true' : 'false' }}">
                                            <option value="" selected hidden>Select Product</option>
                                            @if (!empty($row['products']) && count($row['products'])>0)
                                                @foreach ($row['products'] as $product_item)
                                                    <option value="{{ $product_item['id'] }}">{{ $product_item['name'] }}</option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No products available</option>
                                            @endif
                                        </select>
                                        @error('rows.'.$index.'product')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                                <div class="col-md-1">
                                    @if($isFabricSelected[$index] ?? false)
                                        <label for="pcs_per_mtr_{{$index}}" class="form-label"> Meter </label>
                                        <input type="number" wire:model="rows.{{$index}}.pcs_per_mtr" wire:keyup="updateRowAmount({{$index}})" id="pcs_per_mtr_{{$index}}" class="form-control form-control-sm border border-1 p-2" value="1">
                                    @else
                                        <label for="pcs_per_qty_{{$index}}" class="form-label"> Quantity </label>
                                        <input type="number" wire:model="rows.{{$index}}.pcs_per_qty" wire:keyup="updateRowAmount({{$index}})" id="pcs_per_qty_{{$index}}" class="form-control form-control-sm border border-1 p-2" value="1">
                                    @endif
                                </div>
                                
                                <div class="col-md-2">
                                    @if ($isFabricSelected[$index] ?? false)
                                    <label for="price_per_mtr_{{$index}}" class="form-label">Price/Mtr (Inc. Tax) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="rows.{{$index}}.price_per_mtr"
                                        wire:keyup="updateRowAmount({{ $index }})"  id="price_per_mtr_{{$index}}" class="form-control form-control-sm border border-1 p-2" placeholder="Product Cost Price">
                                    @error('rows.'.$index.'.price_per_mtr')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    @else
                                    <label for="price_per_qty_{{$index}}" class="form-label">Price/Qty (Inc. Tax) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="rows.{{$index}}.price_per_qty"
                                        wire:keyup="updateRowAmount({{ $index }})"  id="price_per_qty_{{$index}}" class="form-control form-control-sm border border-1 p-2" placeholder="Product Cost Price">
                                    @error('rows.'.$index.'.price_per_qty')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <label for="total_amount_{{$index}}" class="form-label">Total Amount</label>
                                    <input type="text" wire:model="rows.{{$index}}.total_amount"  id="total_amount_{{$index}}" class="form-control form-control-sm border border-1 p-2" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    @if ($loop->first)
                                        <button type="button" class="btn btn-success btn-sm mb-0" wire:click="addRow"><i class="fa fa-plus"></i></button>
                                    @else  
                                    <button type="button" class="btn btn-success btn-sm mb-0" wire:click="addRow" style="overflow: visible !important;"><i class="fa fa-plus"></i></button>
                                        <button type="button" class="btn btn-danger btn-sm mb-0" wire:click="removeRow({{ $index }})" style="overflow: visible !important;"><i class="fa fa-times"></i></button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div> 

                <!-- Total Amount -->
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        @php
                            $totalAmount = array_sum(array_column($rows, 'total_amount'));
                        @endphp
                        <h6>Total Amount (Inc. Tax): <span>Rs {{number_format($totalAmount, 2)}}</span></h6>
                    </div>
                </div>
                <!-- Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-outline-success select-md"><i
                            class="material-icons me-1">update</i>Update</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@push('js')
    <!-- Chosen CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css"/>

<!-- jQuery + Chosen JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

    <script>
    function initChosen() {
        $('.chosen-select').chosen({
            width: '100%',
            no_results_text: "No result found"
        }).off('change').on('change', function (e) {
            let model = $(this).attr('wire:model');
            if (model) {
                @this.set(model, $(this).val());
            }
        });

         // Lock preselected values so user can't change
        $('.chosen-select').each(function () {
            if ($(this).data('locked') === 'true') {
                $(this).prop('disabled', true).trigger("chosen:updated");
            }
        });
    }

    document.addEventListener("livewire:navigated", () => {
        initChosen();
    });

    Livewire.hook('morph.updated', ({ el, component }) => {
        initChosen();
    });

    $(document).ready(function () {
        initChosen();
    });
</script>
@endpush