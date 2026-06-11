<div class="container-fluid px-2 px-md-4">
    <section class="admin__title">
        <h5>Create Expense</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ url('admin/accounting/list/depot-expense') }}">Expense</a></li>
            <li>Create Expense</li>
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
                <form wire:submit.prevent="saveExpenses">
                    <div class="row">
                        <!-- User Type Selection -->
                        <div class="mb-3 col-md-6">
                            <label class="form-label"><strong>Expense At <span class="text-danger">*</span></strong></label>
                            <select wire:model="user_type" wire:change="getUser($event.target.value)"  class="form-control form-control bg-white" @if(auth()->guard('admin')->user()->designation == 2) disabled @endif>
                                <option value="" hidden>Select Expense At</option>
                                <option value="staff">Staff</option>
                                <option value="customer">Customer</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        </div>

                        <!-- User Selection Based on Type -->
                        <div class="mb-3 col-md-6">
                            @if($user_type === 'staff')
                                <label class="form-label"><strong>Staff Name <span class="text-danger">*</span></strong></label>
                                <input type="text" wire:model.defer="staffSearchTerm" wire:keyup="searchStaff" class="form-control form-control bg-white" placeholder="Search by staff name" @if(auth()->guard('admin')->user()->designation == 2) disabled @endif>
                                <input type="hidden" wire:model="staff_id">
                                @error('staff_id') <div class="text-danger text-sm">{{ $message }}</div> @enderror

                                @if(!empty($staffSearchResults))
                                    <div class="dropdown-menu show">
                                        @foreach ($staffSearchResults as $staff)
                                            <button type="button" class="dropdown-item" wire:click="selectStaff({{ $staff->id }})">
                                                {{ ucwords($staff->name) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($user_type === 'customer')
                                <label class="form-label"><strong>Customer Name <span class="text-danger">*</span></strong></label>
                                <input type="text" wire:model.defer="customerSearchTerm" wire:keyup="searchCustomer" class="form-control form-control bg-white" placeholder="Search by customer name">
                                <input type="hidden" wire:model="customer_id">
                                @error('customer_id') <div class="text-danger text-sm">{{ $message }}</div> @enderror

                                @if(!empty($customerSearchResults))
                                    <div class="dropdown-menu show">
                                        @foreach ($customerSearchResults as $customer)
                                            <button type="button" class="dropdown-item" wire:click="selectCustomers({{ $customer->id }})">
                                                {{ ucwords($customer->name) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($user_type === 'supplier')
                                <label class="form-label"><strong>Supplier Name <span class="text-danger">*</span></strong></label>
                                <input type="text" wire:model.defer="supplierSearchTerm" wire:keyup="searchSupplier" class="form-control form-control bg-white" placeholder="Search by supplier name">
                                <input type="hidden" wire:model="supplier_id">
                                @error('supplier_id') <div class="text-danger text-sm">{{ $message }}</div> @enderror

                                @if(!empty($supplierSearchResults))
                                    <div class="dropdown-menu show">
                                        @foreach ($supplierSearchResults as $supplier)
                                            <button type="button" class="dropdown-item" wire:click="selectSupplier({{ $supplier->id }})">
                                                {{ ucwords($supplier->name) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                        @if($user_type == 'staff')
                            <div class="mb-3 col-md-4">
                                <label for="expense_id" class="form-label"><strong>Expense<span class="text-danger">*</span></strong> </label>
                                <select wire:model="expense_id" id="expense_id" class="form-control form-control-sm">
                                    <option value="">Select Expense</option>
                                    @foreach($stuffExpenseTitles as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->title }}</option>
                                    @endforeach
                                </select>
                                @error('expense_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($user_type == 'supplier')
                            <div class="mb-3 col-md-4">
                                <label for="expense_id" class="form-label"><strong>Expense<span class="text-danger">*</span></strong> </label>
                                <select wire:model="expense_id" id="expense_id" class="form-control form-control-sm">
                                    <option value="">Select Expense</option>
                                    @foreach($supplierExpenseTitles as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->title }}</option>
                                    @endforeach
                                </select>
                                @error('expense_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        @if($user_type == 'customer')
                            <div class="mb-3 col-md-4">
                                <label for="expense_id" class="form-label"><strong>Expense<span class="text-danger">*</span></strong></label>
                                <select wire:model="expense_id" id="expense_id" class="form-control form-control-sm">
                                    <option value="">Select Expense</option>
                                    @foreach($customerExpenseTitles as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->title }}</option>
                                    @endforeach
                                </select>
                                @error('expense_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <!-- Date -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Date <span class="text-danger">*</span></strong></label>
                            <input type="date" wire:model="payment_date" class="form-control bg-white form-control-sm" placeholder="Select Date" disabled>
                            @error('payment_date') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <!-- Voucher No -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Voucher No</strong></label>
                            <input type="text" wire:model="voucher_no" class="form-control bg-white form-control-sm" readonly>
                        </div>

                        <!-- Amount -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Amount <span class="text-danger">*</span></strong></label>
                            <input type="text" wire:model="amount" class="form-control bg-white form-control-sm" placeholder="Enter Amount">
                            @error('amount') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <!-- Payment Mode -->
                        <div class="mb-3 col-md-4">
                            <label class="form-label"><strong>Mode of Payment <span class="text-danger">*</span></strong></label>
                            <select wire:model="payment_mode" class="form-control" wire:change="ChangePaymentMode($event.target.value)">
                                <option value="" hidden>Select One</option>
                                <option value="cheque">Cheque</option>
                                <option value="neft">NEFT</option>
                                <option value="Cash">Cash</option>
                            </select>
                            @error('payment_mode') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        @if($activePayementMode !== "Cash")
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

                    <!-- Submit Button -->
                    <div class="col-sm-12 mt-2">
                        <button type="submit" class="btn btn-sm btn-success d-flex align-items-center">
                            <i class="material-icons text-white me-1" style="font-size: 15px;">add</i> Add Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
