<?php

namespace App\Http\Livewire\Expense;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Expense;
use App\Models\User;
use App\Models\Supplier;
use App\Models\ExpenseTitle;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class DailyCollection extends Component
{
    use WithFileUploads;

    public $collection_at = '';  // Whether the collection is from "Stuff" or "Supplier"
    public $stuff_id = '';       // Selected Stuff ID
    public $supplier_id = '';    // Selected Supplier ID
    public $expense_title = '';  // Selected Collection Title
    public $amount = '';         // Amount
    public $voucher_no = '';     // Auto-generated voucher number
    public $remarks = '';        // Remarks
    public $image;               // File Upload
    public $stuffOptions = [];   // Stores Staff Names
    public $supplierOptions = []; // Stores Supplier Names
    public $collectionTitles = []; // Stores Expense Titles based on selection

    public function mount()
    {
        $this->generateVoucherNumber();
    }

    public function generateVoucherNumber()
    {
        $latestVoucher = Payment::where('payment_for', 'Credit')->latest()->first();
        $lastVoucherNumber = $latestVoucher ? (int) substr($latestVoucher->voucher_no, 7) : 0;
        $newVoucherNumber = $lastVoucherNumber + 1;
        $this->voucher_no = 'COLLECT' . str_pad($newVoucherNumber, 3, '0', STR_PAD_LEFT);
    }

    public function onCollectionAtChange()
    {
        if ($this->collection_at == '1') {
            // Fetch staff list
            $this->stuffOptions = User::where('user_type', 0)->pluck('name', 'id')->toArray();
            $this->supplierOptions = [];
            $this->supplier_id = null;
            $this->collectionTitles = ExpenseTitle::where('type', 'stuff')->get();
        } elseif ($this->collection_at == '2') {
            // Fetch supplier list
            $this->supplierOptions = Supplier::pluck('name', 'id')->toArray();
            $this->stuffOptions = [];
            $this->stuff_id = null;
            $this->collectionTitles = ExpenseTitle::where('type', 'supplier')->get();
        } else {
            // Reset options
            $this->stuffOptions = [];
            $this->supplierOptions = [];
            $this->stuff_id = null;
            $this->supplier_id = null;
            $this->collectionTitles = [];
        }
    }

    public function submitForm()
    {
        $this->validate([
            'collection_at' => 'required',
            'expense_title' => 'required',
            'amount' => 'required|numeric',
            'voucher_no' => 'nullable|string',
            'remarks' => 'nullable|string',
            'image' => 'nullable|image|max:1024',
            'stuff_id' => 'required_if:collection_at,1|nullable|exists:users,id',
            'supplier_id' => 'required_if:collection_at,2|nullable|exists:suppliers,id',
        ], [
            'collection_at.required' => 'Please select where the collection is being made.',
            'expense_title.required' => 'Please select a collection title.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 1MB.',
            'stuff_id.required_if' => 'The Staff Name is required.',
            'stuff_id.exists' => 'The selected Staff Name is invalid.',
            'supplier_id.required_if' => 'The Supplier Name is required.',
            'supplier_id.exists' => 'The selected Supplier Name is invalid.',
        ]);

        $imagePath = $this->image ? $this->image->store('collections', 'public') : null;

        // Save Collection
        Payment::create([
            'stuff_id' => $this->collection_at == '1' ? $this->stuff_id : null,
            'supplier_id' => $this->collection_at == '2' ? $this->supplier_id : null,
            'expense_id' => $this->expense_title,
            'amount' => $this->amount,
            'voucher_no' => $this->voucher_no,
            'narration' => $this->remarks,
            'image' => $imagePath,
            'payment_for' => 'Credit', // Indicating it's a collection
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Collection recorded successfully.');

        $this->resetExcept('voucher_no');
        $this->generateVoucherNumber();
    }

    public function render()
    {
        return view('livewire.expense.daily-collection');
    }
}
