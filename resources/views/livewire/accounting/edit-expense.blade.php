<div class="container-fluid px-2 px-md-4">
    <section class="admin__title">
        <h5>Edit Expense</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ url('admin/accounting/list/depot-expense') }}">Expense</a></li>
            <li>Edit Expense</li>
            <li class="back-button">
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0" href="{{ url('admin/accounting/list/depot-expense') }}" role="button">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    <span class="ms-1">Back</span>
                </a>
            </li>
        </ul>
    </section>
    <div class="card card-body">
        <div class="card card-plain h-100">
            <div class="card-body p-3">
                @if (session()->has('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif
                <form wire:submit.prevent="updateExpense">
                    <div class="row">
                        <!-- User Type Selection -->
                       
                        <!-- Date -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Date <span class="text-danger">*</span></strong></label>
                            <input type="date" wire:model="payment_date" class="form-control bg-white" placeholder="Select Date">
                            @error('payment_date') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <!-- Voucher No -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Voucher No</strong></label>
                            <input type="text" wire:model="voucher_no" class="form-control" readonly>
                        </div>

                        <!-- Amount -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Amount <span class="text-danger">*</span></strong></label>
                            <input type="text" wire:model="amount" class="form-control bg-white" placeholder="Enter Amount">
                            @error('amount') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <!-- Payment Mode -->
                        <div class="mb-3 col-md-4">
                        <label class="form-label"><strong>Mode of Payment <span class="text-danger">*</span></strong></label>
                        <select wire:model="payment_mode" class="form-control" wire:change="ChangePaymentMode($event.target.value)">
                            <option value="" disabled>Select One</option>
                            <option value="cheque">Cheque</option>
                            <option value="neft">NEFT</option>
                            <option value="Cash">Cash</option>
                        </select>
                        @error('payment_mode') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Show Fields Only When Payment Mode Is Not Cash -->
                    @if($activePaymentMode !== "Cash")
                        <!-- Cheque / UTR No -->
                        <div class="col-sm-4">
                            <label class="form-label"><strong>Cheque No / UTR No</strong></label>
                            <input type="text" wire:model="chq_utr_no" class="form-control" placeholder="Enter Cheque or UTR No">
                            @error('chq_utr_no') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <!-- Bank Name -->
                        <div class="col-sm-4">
                            <label class="form-label"><strong>Bank Name</strong></label>
                            <input type="text" wire:model="bank_name" class="form-control" placeholder="Enter Bank Name">
                            @error('bank_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @endif

                        <!-- Narration -->
                        <div class="col-sm-12">
                            <label class="form-label"><strong>Narration</strong></label>
                            <textarea wire:model="narration" class="form-control" rows="3" placeholder="Enter Narration"></textarea>
                            @error('narration') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 mt-2">
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-sm btn-success d-flex align-items-center">
                            <i class="material-icons text-white me-1" style="font-size: 15px;">update</i> Update Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
