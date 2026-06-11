
<div class="container-fluid row mb-4">
    <div class="col-12 d-flex justify-content-end mb-3">
        <a href="{{ route('admin.order.index') }}" class="btn btn-dark">
            <i class="material-icons text-white">chevron_left</i> Back
        </a>
    </div>
    <!-- Left Column: Transaction History -->
    <div class="{{$totalRemaining > 0 ? 'col-lg-8' : 'col-lg-12'}} col-md-6 mb-4">
        <div class="card my-4">
        
            <div class="card-header pb-0">
                <div class="row">
                    <div class="col-lg-6 col-7">
                        <h6>Ledger View</h6>
                    </div>
                    
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle">
                                <th>Date</th>
                                <th>Transaction Type</th>
                                <th>Paid Amount</th>
                                <!-- <th>Remaining Amount</th> -->
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M d, Y') }}</td>
                                    <td class="text-center">{{ $transaction->transaction_type }}</td>
                                    <td class="text-center">{{ number_format($transaction->paid_amount, 2) }}</td>
                                   
                                    <td class="text-center">{{ $transaction->remarks ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No transactions found for this order.</td>
                                </tr>
                            @endforelse
                            <tr class="text-center text-uppercase text-secondary font-weight-bolder opacity-7 align-middle">
                                <td colspan="2">Total Amount: {{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-center">Total Paid Amount: {{ number_format($totalPaid, 2) }}</td>
                                <td class="text-center {{ $totalRemaining > 0 ? 'text-danger' : 'text-success' }}">
                                Total Remaining Amount: {{ number_format($totalRemaining, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>  

    <!-- Right Column: Add Payment Form -->
    @if ($totalRemaining > 0)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card my-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div>
                            <h6>Add Payment<span style="color: red;"> ({{env('ORDER_PREFIX').$order->order_number}})</span></h6>
                        </div>
                        <div class="row">
                            @if(session()->has('error'))
                                <div class="alert alert-danger" id="flashMessage">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <form wire:submit.prevent="addPayment">
                        <div class="row p-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Payment method</label>
                                    <select class="form-control border border-2 p-2 form-control-sm @error('payment_method') border-danger @enderror" 
                                        wire:model="payment_method" >
                                        <option value="" selected hidden>Choose one...</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online</option>
                                    </select>
                                    @error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid_amount">Paid Amount</label>
                                    <input type="number" 
                                        wire:model="paid_amount" 
                                        class="form-control border border-2 p-2 form-control-sm @error('paid_amount') border-danger @enderror" 
                                        required>
                                    @error('paid_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea 
                                        wire:model="remarks" 
                                        class="form-control border border-2 p-2 form-control-sm @error('remarks') border-danger @enderror"></textarea>
                                    @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-12 text-end mt-2">
                                <input type="checkbox" wire:click= "togglePayment" id="allowPayment">Are you sure to add payment?
                                <button type="submit" class="btn btn-primary btn-sm mt-1" wire:loading.attr="disabled" @if (!$allowPayment)
                                    disabled
                                @endif>
                                    <span wire:loading.remove>Add Payment</span>
                                    <span wire:loading><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                                </button>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>   
    @endif
</div>
