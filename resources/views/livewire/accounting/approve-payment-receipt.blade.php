<div class="card p-4">
    <h4 class="mb-4">Payment Receipt Details</h4>

    <div class="row">
        {{-- Customer --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Customer</label>
                <input type="text" class="form-control" value="{{ $payment->customer->name ?? '' }}" disabled>
            </div>
        </div>

        {{-- Collected By --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Collected By</label>
                <input type="text" class="form-control"
                    value="{{ $staffs->firstWhere('id', $staff_id)->name ?? '' }}" disabled>
            </div>
        </div>

        {{-- Amount --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Amount</label>
                <input type="text" class="form-control" value="{{ $amount }}" disabled>
            </div>
        </div>

        {{-- Voucher No --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Voucher No</label>
                <input type="text" class="form-control" value="{{ $voucher_no }}" disabled>
            </div>
        </div>

        {{-- Date --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Date</label>
                <input type="date" class="form-control" value="{{ $payment_date }}" disabled>
            </div>
        </div>

        {{-- Next Payment Date --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Next Payment Date</label>
                <input type="date" class="form-control" value="{{ $next_payment_date }}" disabled>
            </div>
        </div>

        {{-- Mode of Payment --}}
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Mode of Payment</label>
                <input type="text" class="form-control" value="{{ ucfirst($payment_mode) }}" disabled>
            </div>
        </div>

        {{-- Cheque / NEFT Fields --}}
        @if($activePayementMode != 'cash' && $activePayementMode != 'digital_payment')
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Cheque / UTR No</label>
                    <input type="text" class="form-control" value="{{ $chq_utr_no }}" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Bank Name</label>
                    <input type="text" class="form-control" value="{{ $bank_name }}" disabled>
                </div>
            </div>
        @endif

        {{-- Digital Payment --}}
        @if($activePayementMode == 'digital_payment')
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Transaction No</label>
                    <input type="text" class="form-control" value="{{ $transaction_no }}" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Withdrawal Charge</label>
                    <input type="text" class="form-control" value="{{ $withdrawal_charge }}" disabled>
                </div>
            </div>
        @endif

        {{-- Cheque Only --}}
        @if($activePayementMode == 'cheque')
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Deposit Date</label>
                    <input type="date" class="form-control" value="{{ $deposit_date }}" disabled>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Amount Credit Date</label>
                    <input type="date" class="form-control" value="{{ $credit_date }}" disabled>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Cheque Photo</label>
                    @if($cheque_file)
                        <a href="{{ asset($cheque_file) }}" target="_blank">View</a>
                    @else
                        <span>No file</span>
                    @endif
                </div>
            </div>
        @endif
         @if($activePayementMode == 'cash' || $activePayementMode == 'neft')
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Receipt Copy Upload</label><br/>
                    @if($receipt_copy_upload)
                        <a class="btn btn-outline-success btn-sm select-md" href="{{ asset($receipt_copy_upload) }}" target="_blank">View</a>
                    @else
                        <span>No file</span>
                    @endif
                </div>
            </div>
         @endif

        {{-- Narration --}}
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label>Narration</label>
                <textarea class="form-control" disabled>{{ $narration }}</textarea>
            </div>
        </div>
        
         <div class="row mt-4">
            <div class="col-sm-12">
        
               
                    <button wire:click="approvePayment"
                        onclick="confirm('Approve this payment?') || event.stopImmediatePropagation()"
                        class="btn btn-sm btn-outline-success select-md">
                        Approve Payment
                    </button>
        
                <a href="{{route('admin.accounting.payment_collection')}}"
                   class="btn btn-sm btn-outline-danger select-md">
                    Back
                </a>
        
            </div>
        </div>
    </div>
</div>