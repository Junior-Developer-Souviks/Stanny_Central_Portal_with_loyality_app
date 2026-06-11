<div class="container">
    <section class="admin__title">
        <h5>Add Opening Balance</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ route('admin.accounting.add_opening_balance') }}">Opening Balance</a></li>
            <li>Add Opening Balance</li>
            <li class="back-button">
                <a href="{{route('admin.accounting.list_opening_balance')}}" class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0">
                    <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                    Back</a>
            </li>
        </ul>
    </section>
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="submitForm">
                @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="customer">Customer <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" wire:model="customer" wire:keyup="findCustomer($event.target.value)"
                                    class="form-control form-control-sm" placeholder="Search Customer">
                                    <input type="hidden" wire:model="customer_id" value="">
                                    @if(isset($errorMessage['customer_id']))
                                        <div class="text-danger">{{ $errorMessage['customer_id'] }}</div>
                                    @endif
                                @if(!empty($searchResults))
                                <div id="fetch_customer_details" class="dropdown-menu show w-100"
                                    style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($searchResults as $customer)
                                    <button class="dropdown-item" type="button"
                                        wire:click="selectCustomer({{ $customer->id }})">
                                        <img src="{{ $customer->profile_image ? asset($customer->profile_image) : asset('assets/img/user.png') }}"
                                            alt=""> {{ ucfirst($customer->prefix . " ".$customer->name) }} ({{ $customer->phone }})
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="credit_debit">Credit / Debit <span class="text-danger">*</span></label>
                            <select wire:model="credit_debit" wire:change="updateCreditDebit($event.target.value)"
                                class="form-control form-control-sm">
                                <option value="" hidden>Select One</option>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </select>
                            @if(isset($errorMessage['credit_debit']))
                                <div class="text-danger">{{ $errorMessage['credit_debit'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="payment_type">Bank / Cash / Bank + Cash <span
                                    class="text-danger">*</span></label>
                            <select wire:model="payment_type" wire:change="UpdatePaymentType($event.target.value)" class="form-control form-control-sm">
                                <option value="" hidden>Select One</option>
                                <option value="bank">Bank</option>
                                <option value="cash">Cash</option>
                                <option value="bank_cash">Bank + Cash</option>
                            </select>
                            @if(isset($errorMessage['payment_type']))
                                <div class="text-danger">{{ $errorMessage['payment_type'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="date" class="form-control form-control-sm">
                            @if(isset($errorMessage['date']))
                                <div class="text-danger">{{ $errorMessage['date'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="voucher_no">Voucher No <span class="text-danger">*</span></label>
                            <input type="text" wire:model="voucher_no" class="form-control form-control-sm"
                                value="{{$voucher_no}}" disabled>
                            @if(isset($errorMessage['voucher_no']))
                                <div class="text-danger">{{ $errorMessage['voucher_no'] }}</div>
                            @endif
                        </div>
                    </div>
                    @if ($showBankFields)
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" wire:model="amount" class="form-control form-control-sm">
                                @if(isset($errorMessage['amount']))
                                <div class="text-danger">{{ $errorMessage['amount'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!-- Cash Amount (Only for Bank + Cash) -->
                    @if ($showCashAmount)
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="bank_amount">Bank Amount <span class="text-danger">*</span></label>
                            <input type="number" wire:model="bank_amount" class="form-control form-control-sm">
                            @if(isset($errorMessage['bank_amount']))
                                <div class="text-danger">{{ $errorMessage['bank_amount'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="cash_amount">Cash Amount <span class="text-danger">*</span></label>
                            <input type="number" wire:model="cash_amount" class="form-control form-control-sm">
                            @if(isset($errorMessage['cash_amount']))
                                <div class="text-danger">{{ $errorMessage['cash_amount'] }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if ($showPaymentMode)
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" wire:model="bank_name" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="payment_mode">Cheque / NEFT <span class="text-danger">*</span></label>
                            <select wire:model="payment_mode" class="form-control form-control-sm">
                                <option value="" hidden>Select One</option>
                                <option value="cheque">Cheque</option>
                                <option value="neft">NEFT</option>
                            </select>
                            @if(isset($errorMessage['payment_mode']))
                                <div class="text-danger">{{ $errorMessage['payment_mode'] }}</div>
                           @endif
                        </div>
                    </div>
                    {{-- @if ($payment_mode !== 'cash') --}}
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="transaction_no">Cheque No / UTR No</label>
                            <input type="text" wire:model="transaction_no" class="form-control form-control-sm">
                        </div>
                    </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <div class="form-group mb-3">
                            <label for="narration">Narration</label>
                            <textarea wire:model="narration" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="material-icons text-white" style="font-size: 15px;">add</i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>