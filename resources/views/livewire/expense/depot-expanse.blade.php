<div x-data="{ activeTab: @entangle('activeTab') }">
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" :class="{ 'active': activeTab === 'dailyCollection' }"
               @click="activeTab = 'dailyCollection'; $wire.set('activeTab', 'dailyCollection');">
                Daily Collection
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" :class="{ 'active': activeTab === 'dailyExpenses' }"
               @click="activeTab = 'dailyExpenses'; $wire.set('activeTab', 'dailyExpenses');">
                Daily Expenses
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-3">
        <!-- Daily Expenses Tab -->
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'dailyExpenses' }">
            <div class="row mb-4">
                <div class="d-flex justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <label for="start_date" class="ms-2">Start Date</label>
                        <input type="date" id="start_date" wire:model="start_date" class="form-control border border-1 ms-2">

                        <label for="end_date" class="ms-2">End Date</label>
                        <input type="date" id="end_date" wire:model="end_date" class="form-control border border-1 ms-2">

                        <!-- <input type="text" wire:model.debounce.500ms="search" class="form-control border border-2 p-2 custom-input-sm" placeholder="Search By Name"> -->
                        <input type="text" wire:model.debounce.500ms="search" class="form-control border border-2 p-2 custom-input-sm" placeholder="Search by Stuff ID or Supplier ID">
                        <button type="button" wire:click="$refresh" class="btn btn-dark text-light mb-0 custom-input-sm ms-2">
                            <span class="material-icons">search</span>
                        </button>
                        <a href="" class="btn btn-dark text-light mb-0 custom-input-sm ms-2">
                            <span class="material-icons">refresh</span>
                        </a>
                    </div>
                    <div class="col-md-4 text-end">
                        <a type="button" class="btn btn-primary btn-sm" href="{{route('admin.accounting.daily.expenses')}}">
                            + Add Expense
                        </a>
                    </div>
                </div>

                <div class="col-lg-12 col-md-6">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <h6>Daily Expenses</h6>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Paid Amount</th>
                                            <th>Expense For</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $payment)
                                            <tr>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($payment->updated_at)->format('d-m-Y') }}</td>
                                                <td class="text-center">  {{ $payment->staff?->name ?? $payment->supplier?->name ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $payment->amount }}</td>
                                                <td class="text-center">{{ $payment->expense->title }}</td>
                                                <td class="text-center">{{ $payment->narration ?? 'No remarks' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No transactions found.</td>
                                            </tr>
                                        @endforelse
                                        <tr class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">
                                            <td>Total Amount: {{ number_format($totalAmount, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Expense Modal -->
                <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Expense</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form wire:submit.prevent="addExpense">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select class="form-control" wire:model="payment_method">
                                            <option value="" hidden>Select...</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Online">Online</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>Paid Amount</label>
                                        <input type="number" wire:model="paid_amount" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>Remarks</label>
                                        <textarea wire:model="remarks" class="form-control"></textarea>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Add Expense
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Daily Collection Tab -->
        <!-- Daily Collection Tab -->
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'dailyCollection' }">
            <div class="row mb-4">
                <div class="d-flex justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <label for="start_date_collection" class="ms-2">Start Date</label>
                        <input type="date" id="start_date_collection" wire:model="start_date_collection" class="form-control border border-1 ms-2">

                        <label for="end_date_collection" class="ms-2">End Date</label>
                        <input type="date" id="end_date_collection" wire:model="end_date_collection" class="form-control border border-1 ms-2">

                        <input type="text" wire:model.debounce.500ms="search_collection" class="form-control border border-2 p-2 custom-input-sm" placeholder="Search by Staff ID or Customer ID">
                        <button type="button" wire:click="$refresh" class="btn btn-dark text-light mb-0 custom-input-sm ms-2">
                            <span class="material-icons">search</span>
                        </button>
                        <a href="" class="btn btn-dark text-light mb-0 custom-input-sm ms-2">
                            <span class="material-icons">refresh</span>
                        </a>
                    </div>
                
                    <div class="col-md-4 text-end">
                        <a type="button" class="btn btn-primary btn-sm" href="{{route('daily-collection.add')}}">
                        + Add Collection
                        </a>
                    </div>
                </div>

                <div class="col-lg-12 col-md-6">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <h6>Daily Collections</h6>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Received Amount</th>
                                            <th>Collection For</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($collections as $collection)
                                            <tr>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($collection->updated_at)->format('d-m-Y') }}</td>
                                                <td class="text-center"> {{ $collection->staff?->name ?? $collection->customer?->name ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $collection->amount }}</td>
                                                <td class="text-center">{{ $collection->expense->title }}</td>
                                                <td class="text-center">{{ $collection->narration ?? 'No remarks' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No collections found.</td>
                                            </tr>
                                        @endforelse
                                        <tr class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">
                                            <td>Total Amount: {{ number_format($totalCollection, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Collection Modal -->
                <div class="modal fade" id="addCollectionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Collection</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form wire:submit.prevent="addCollection">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select class="form-control" wire:model="payment_method">
                                            <option value="" hidden>Select...</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Online">Online</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>Received Amount</label>
                                        <input type="number" wire:model="received_amount" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>Remarks</label>
                                        <textarea wire:model="remarks" class="form-control"></textarea>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Add Collection
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
