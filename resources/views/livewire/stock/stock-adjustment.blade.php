<div class="container">
    <section class="admin__title">
        <h5>Stock Adjustment Log</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Stock Adjustment</li>
            <li>Stock</li>
            <li class="back-button"></li>
        </ul>
    </section>
    <div class="search__filter">
        <div class="row align-items-center justify-content-end">

            <div class="col-auto">
                <div class="row g-3 align-items-center">
                   
                    <div class="col-md-auto mt-3">
                        <button class="btn btn-outline-success select-md" data-bs-toggle="modal"
                            data-bs-target="#stockAdjustModal">Stock Adjustment</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div wire:ignore.self class="modal fade" id="stockAdjustModal" tabindex="-1" aria-labelledby="stockAdjustModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockAdjustModalLabel">Upload Stock Adjustment Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form wire:submit.prevent="uploadStockAdjustment">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Upload CSV/XLSX</label>
                            <input type="file" wire:model="csvFile" class="form-control">
                            <div wire:loading wire:target="csvFile" class="mt-2 text-center">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Uploading...
                            </div>
                            @error('csvFile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <p>Download a sample CSV file for reference:</p>
                            <a href="{{ asset('assets/csv/stock_adjustment_sample.csv') }}"
                                class="btn btn-sm btn-outline-primary" download>
                                Download Sample CSV
                            </a>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button class="btn btn-sm btn-success" >
                            Upload & Process
                        </button>
                    </div>
                </form>
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
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Time</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Fabric / Product</th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Adjustment Stock</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Old Qty</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        New Qty</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Remarks</th>

                                </tr>
                            </thead>
                            
                            <tbody>
                            {{-- NOTE: You should remove @dd($logData) before using the page --}}
                            {{-- @dd($logData)  --}}
                            @forelse ($logData as $index => $stock_adjust_data)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">
                                        {{ $stock_adjust_data->created_at?->format('d-m-Y H:i') ?? 'N/A' }}
                                    </p>
                                </td>
                                
                                <td>
                                    {{-- Use the ID from the corresponding column --}}
                                    <div class="badge bg-success">
                                        @if ($stock_adjust_data->fabric_id)
                                            Fabric: {{ $stock_adjust_data->fabric ?  $stock_adjust_data->fabric->title : "N/A" }}
                                        @elseif ($stock_adjust_data->product_id)
                                            Product: {{ $stock_adjust_data->product ? $stock_adjust_data->product->name : "N/A" }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="text-xs font-weight-bold mb-0 
                                        @if($stock_adjust_data->adjustment < 0) text-danger @else text-success @endif">
                                        {{ $stock_adjust_data->adjustment }}
                                    </span>
                                </td>
                                
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">
                                        {{ $stock_adjust_data->old_qty }}
                                    </p>
                                </td>
                                
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">
                                        {{ $stock_adjust_data->new_qty }}
                                    </p>
                                </td>
                                
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">
                                        {{ $stock_adjust_data->remarks }}
                                    </p>
                                </td>
                                
                                {{-- REMOVED all irrelevant Purchase Order columns and action buttons --}}
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <span class="text-xs text-secondary mb-0">No stock adjustment logs found.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        </table>
                        <div class="mt-4">
                           {{ $logData->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <script>
        window.addEventListener('close_modal', event => {
            let modalEl = document.getElementById('stockAdjustModal');
            let modal = bootstrap.Modal.getInstance(modalEl); // Get existing modal instance
            if (modal) {
                modal.hide();
            }
        });

    </script>