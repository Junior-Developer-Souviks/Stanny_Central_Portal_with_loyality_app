<?php

namespace App\Http\Livewire\Expense;

use Livewire\Component;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Stuff;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class DailyCollection extends Component
{
    use WithFileUploads;

    public $collection_at = '';  // Stores whether "Stuff" or "Supplier" is selected
    public $stuff_id = '';    // Selected Stuff ID
    public $supplier_id = ''; // Selected Supplier ID
    public $collection_title = ''; // Selected Collection Title
    public $amount = ''; // Amount
    public $voucher_no = ''; // Voucher Number
    public $remarks = ''; // Remarks
    public $image; // File Upload for Image
    public $stuffOptions = [];    // Stores Stuff Names
    public $supplierOptions = []; // Stores Supplier Names
    public $stuffCollectionTitles = [];  // Holds Stuff Collection Titles
    public $supplierCollectionTitles = [];
    public $activeTab = 'dailyCollection'; // Default tab

   
    // Called when the 'collection_at' dropdown value changes
    public function onCollectionAtChange()
    {
        if ($this->collection_at == '1') {
            // Fetch Stuff list (user_type = 0 means stuff)
            $this->stuffOptions = User::where('user_type', 0)->pluck('name', 'id')->toArray();
            $this->supplierOptions = []; // Clear supplier options
            $this->supplier_id = null;   // Reset supplier selection
            $this->stuffCollectionTitles = Expense::where('for_staff', 1)->where('for_credit',1)->get(); 
            $this->supplierCollectionTitles = []; // Clear Supplier Titles
        } elseif ($this->collection_at == '2') {
            // Fetch Supplier list
            $this->supplierOptions = Supplier::pluck('name', 'id')->toArray();
            $this->stuffOptions = [];   // Clear stuff options
            $this->stuff_id = null;     // Reset stuff selection
            $this->supplierCollectionTitles = Expense::where('for_partner', 1)->where('for_credit',1)->get();
            $this->stuffCollectionTitles = [];
        } else {
            // If nothing is selected, reset both options
            $this->stuffOptions = [];
            $this->supplierOptions = [];
            $this->stuff_id = null;
            $this->supplier_id = null;
            $this->stuffCollectionTitles = [];
            $this->supplierCollectionTitles = [];
        }
    }
    public function mount()
    {
        $this->activeTab = request()->query('activeTab', 'dailyCollection');

        // Auto-generate voucher number
        $this->generateVoucherNumber();
    }

    public function generateVoucherNumber()
    {
        // Get the latest voucher number
        // $latestVoucher = Payment::latest()->first();
        $latestVoucher = Payment::where('payment_for', 'Credit')
        ->orderByDesc('voucher_no') // Ensure correct sorting
        ->first();
        // dd( $latestVoucher->voucher_no);
        // Extract the numeric part from the latest voucher number
        $lastVoucherNumber = $latestVoucher ? (int) substr($latestVoucher->voucher_no, 10) : 0;

        // Increment the voucher number by 1
        $newVoucherNumber = $lastVoucherNumber + 1;

        // Format the new voucher number as EXPENSE001, EXPENSE002, etc.
        $this->voucher_no = 'COLLECTION' . str_pad($newVoucherNumber, 3, '0', STR_PAD_LEFT);
    }
    public function submitForm()
    {
        $this->validate([
            'collection_at' => 'required',
            'collection_title' => 'required',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
            'image' => 'required|image|max:1024', // Validate the image upload
        
            // Conditional validation
            'stuff_id' => 'required_if:collection_at,1|nullable|exists:users,id',
            'supplier_id' => 'required_if:collection_at,2|nullable|exists:suppliers,id',
        ], [
            'collection_at.required' => 'Please select where the collection is being made.',
            'collection_title.required' => 'Please select an collection title.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'image.required' => 'Please upload an image for this collection.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 1MB.',
        
            // Custom messages for conditional fields
            'stuff_id.required_if' => 'The Stuff Name is required .',
            'stuff_id.exists' => 'The selected Stuff Name is invalid.',
            'supplier_id.required_if' => 'The Supplier Name is required.',
            'supplier_id.exists' => 'The selected Supplier Name is invalid.',
        ]);
        

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('collections', 'public');
        }

        // Create Payment Entry
        $payment = Payment::create([
            'stuff_id' => $this->collection_at == '1' ? $this->stuff_id : null,
            'supplier_id' => $this->collection_at == '2' ? $this->supplier_id : null,
            // 'collection_id' => $this->collection_title,
            'expense_id' => $this->collection_title,
            'amount' => $this->amount,
            'voucher_no' => $this->voucher_no,
            'narration' => $this->remarks,
            'image' => $imagePath,
            'payment_for' => 'Credit',
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Collection added successfully!');
        $this->reset(); // Reset the form fields
    }


    public function render()
    {
        return view('livewire.expense.daily-collection');
    }
}
