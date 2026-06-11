<div>

    <section>
        <ul class="breadcrumb_menu">
            <li>Accounting</li>
            <li>Expense Approval</li>
        </ul>

        <div class="row">
           
                
            <div class="col-md-12">

                <div class="card">
                    <div class="card-body">
                    
                        {{-- Expense At --}}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Expense At</label>
                                    <input type="text" class="form-control"
                                        value="{{ ucfirst($payment->user_type) }}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Date</label>
                                    <input type="date" class="form-control"
                                        value="{{ $payment->payment_date }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- User --}}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>{{ ucfirst($payment->user_type) }}</label>
                                    <input type="text" class="form-control"
                                        value="@if($payment->staff) {{ $payment->staff->name }}
                                               @elseif($payment->customer) {{ $payment->customer->name }}
                                               @elseif($payment->supplier) {{ $payment->supplier->name }}
                                               @elseif($payment->partner) {{ $payment->partner->name }}
                                               @else - @endif"
                                        disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Expense</label>
                                    <input type="text" class="form-control"
                                        value="{{ $payment->expense->title ?? '-' }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Voucher / Amount / Mode --}}
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <label>Voucher No</label>
                                    <input type="text" class="form-control"
                                        value="{{ $payment->voucher_no }}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <label>Amount</label>
                                    <input type="text" class="form-control"
                                        value="{{ number_format($payment->amount,2) }}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <label>Mode of Payment</label>
                                    <input type="text" class="form-control"
                                        value="{{ strtoupper($payment->payment_mode) }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Bank / UTR --}}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Cheque / UTR No</label>
                                    <input type="text" class="form-control"
                                        value="{{ $payment->chq_utr_no }}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Bank Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $payment->bank_name }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Narration --}}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label>Narration</label>
                                    <textarea class="form-control" disabled>{{ $payment->narration }}</textarea>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label class="d-block mb-2">Receipt Copy</label>
                            
                                    @if($payment->expense_proof)
                                        <a href="{{ asset($payment->expense_proof) }}" target="_blank">
                                            <img src="{{ asset($payment->expense_proof) }}"
                                                 alt="Expense Proof"
                                                 class="img-fluid border"
                                                 style="max-height:250px;">
                                        </a>
                                    @else
                                        <p>No receipt uploaded</p>
                                    @endif
                                </div>
                            </div>
                           
                        </div>

                        {{-- ACTIONS --}}
                        <div class="row mt-4">
                            <div class="col-sm-12">

                                @if($payment->created_from == 'app' && $payment->is_ledger_added == 0)
                                    <button wire:click="approveExpense"
                                        onclick="confirm('Approve this expense?') || event.stopImmediatePropagation()"
                                        class="btn btn-sm btn-outline-success select-md">
                                        Approve Expense
                                    </button>
                                @endif

                                <a href="{{route('admin.accounting.list.depot_expense')}}"
                                   class="btn btn-sm btn-outline-danger select-md">
                                    Back
                                </a>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</div>