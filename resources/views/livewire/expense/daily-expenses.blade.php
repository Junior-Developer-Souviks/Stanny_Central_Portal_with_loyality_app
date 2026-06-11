<div class="container-fluid px-2 px-md-4">
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card card-body">
        <h4 class="m-0">Add Daily Expense</h4>
        <div class="card card-plain h-100">
            <div class="card-header pb-0 p-3">
                <div class="row">
                    <div class="col-md-8 d-flex align-items-center">
                        <h6 class="badge bg-danger custom_danger_badge">Expense Information</h6>
                    </div>
                    <div class="col-md-4 text-end">
                        <!-- <a href="#" class="btn btn-cta">
                            <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i> Back
                        </a> -->
                        <a href="{{ route('depot-expense.index', ['activeTab' => $activeTab]) }}" class="btn btn-cta">
                            <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i> Back
                        </a>
                    </div>
                </div>
            </div>
           

            <div class="card-body p-3">
                <form wire:submit.prevent="submitForm" enctype="multipart/form-data">
                    <!-- Basic Expense Information Section -->
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-danger custom_danger_badge">Expense Details</h6>
                    </div>
                    <div class="row mb-3">
                        <div class="mb-3 col-md-4">
                            <label for="expense_at" class="form-label">Expense At</label>
                            <select wire:model="expense_at" wire:change="onExpenseAtChange" id="expense_at" class="form-control form-control-sm">
                                <option value="" disabled selected>Select One</option>
                                <option value="1">Stuff</option>
                                <option value="2">Supplier</option>
                            </select>
                            @error('expense_at') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Stuff Name Section (Visible when "Stuff" is selected) -->
                        @if($expense_at == '1')
                            <div class="mb-3 col-md-4">
                                <label for="stuff_id" class="form-label">Stuff Name</label>
                                <select wire:model="stuff_id" id="stuff_id" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select One</option>
                                    @foreach($stuffOptions as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('stuff_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Supplier Name Section (Visible when "Supplier" is selected) -->
                        @if($expense_at == '2')
                            <div class="mb-3 col-md-4">
                                <label for="supplier_id" class="form-label">Supplier Name</label>
                                <select wire:model="supplier_id" id="supplier_id" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select One</option>
                                    @foreach($supplierOptions as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($expense_at == '1')
                            <div class="mb-3 col-md-4">
                                <label for="expense_title" class="form-label">Expense Title</label>
                                <select wire:model="expense_title" id="expense_title" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select Expense Title</option>
                                    @foreach($stuffExpenseTitles as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->title }}</option>
                                    @endforeach
                                </select>
                                @error('expense_title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($expense_at == '2')
                            <div class="mb-3 col-md-4">
                                <label for="expense_title" class="form-label">Expense Title</label>
                                <select wire:model="expense_title" id="expense_title" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select Expense Title</option>
                                    @foreach($supplierExpenseTitles as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->title }}</option>
                                    @endforeach
                                </select>
                                @error('expense_title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="mb-3 col-md-4">
                            <label for="amount" class="form-label">Amount</label>
                            <input wire:model="amount" type="number" id="amount" class="form-control form-control-sm" placeholder="Enter Amount">
                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-danger custom_danger_badge">Payment Information</h6>
                    </div>
                    <div class="row mb-3">
                        

                        <div class="mb-3 col-md-4">
                            <label for="voucher_no" class="form-label">Voucher No</label>
                            <input wire:model="voucher_no" type="text" id="voucher_no" class="form-control form-control-sm" placeholder="Voucher Number" readonly>
                            @error('voucher_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>


                        <div class="mb-3 col-md-4">
                            <label for="image" class="form-label">Upload File</label>
                            <input wire:model="image" type="file" id="image" class="form-control form-control-sm">
                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                         <!-- Remarks Section -->
                   
                        <div class="mb-3 col-md-4">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea wire:model="remarks" id="remarks" class="form-control form-control-sm" rows="4" placeholder="Enter any remarks here..."></textarea>
                            @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cta mt-3">Save Expense</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if (session()->has('message'))
    <div class="alert alert-success mt-3">
        {{ session('message') }}
    </div>
@endif
