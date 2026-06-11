<div>
    <form wire:submit.prevent="updateExpense">
        <!-- Voucher Number (Readonly) -->
        <div class="form-group">
            <label for="voucher_no">Voucher Number</label>
            <input type="text" wire:model="voucher_no" id="voucher_no" class="form-control" readonly>
        </div>

        <!-- Amount Field -->
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="text" wire:model="amount" id="amount" class="form-control" required>
            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Payment Date Field -->
        <div class="form-group">
            <label for="payment_date">Payment Date</label>
            <input type="date" wire:model="payment_date" id="payment_date" class="form-control" required>
            @error('payment_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Payment Mode Field -->
        <div class="form-group">
            <label for="payment_mode">Payment Mode</label>
            <select wire:model="payment_mode" id="payment_mode" class="form-control" required>
                <option value="">Select Mode</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
            </select>
            @error('payment_mode') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update Expense</button>
    </form>
</div>
