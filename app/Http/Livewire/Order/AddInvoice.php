<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Ledger;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\PackingSlip;
use App\Models\Payment;
use NumberToWords\NumberToWords;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use App\Interfaces\AccountingRepositoryInterface;


class AddInvoice extends Component
{
    protected $accountingRepository;
    public $rows = []; 
    public $collections;
    public $totalAmount = 0;
    public $totalInWords = '';
    public $products;
    public $selectedCustomerId;
    public $customers,$searchTerm,$customer_name,$voucher_no,$payment_collection_id,$staff_id,$customer_id,$invoice_date,$due_date,$source,$reference,$due_amount,$ht_amount,$tva_amount,$ca_amount;
    public $salesmen;
    public $salesman;
    public $bill_book = [];
    public $order_number;
    public $showPaymentReceipt = false;
    public $payment_date,$payment_mode,$chq_utr_no,$bank_name,$actual_amount,$amount =0;
    public $showPaymentFields = false;
    public $receipt_for = "Customer";

    protected $listeners = ['paymentConfirmed' => 'showPayment'];
    
    public function boot(AccountingRepositoryInterface $accountingRepository){
        $this->accountingRepository = $accountingRepository;
    }

    public function showPayment()
    {
        $this->showPaymentReceipt = true;
    }
    protected function rules()
    {
        $rules = [
            'customer_name' => [
                'max:255',
                function ($attribute, $value, $fail) {
                    // If no customer is selected
                    if (empty($this->selectedCustomerId)) {
                        if (empty($value)) {
                            $fail('Please enter customer name.');
                        }
                    }
                    // If customer is selected, no need to validate input
                },
            ],

            'invoice_date'  => 'required|date',
            'due_date'      => 'required|date|after_or_equal:invoice_date',
            'source'        => 'required|string|unique:orders,source|max:255',
            'reference'     => 'required|string|unique:orders,reference|max:255',
            'salesman'      => 'required',
            'order_number' => 'required|not_in:000'
        ];


        foreach ($this->rows as $index => $row) {
            $rules["rows.$index.product_id"] = 'required|exists:products,id';
            $rules["rows.$index.unit_price"] = 'required|numeric';
        }

        return $rules;
    }

    public function changeSalesman($value){
        $this->bill_book = Helper::generateInvoiceBill($value);
        $this->order_number = $this->bill_book['number'];
        $this->bill_id = $this->bill_book['bill_id'] ?? null;
    }

    protected function messages()
    {
        $messages = [
            // 'customer_name.required' => 'Customer name is required.',
            'invoice_date.required'  => 'Invoice date is required.',
            'due_date.required'      => 'Due date is required.',
            'source.required'        => 'Source is required.',
            'reference.required'     => 'Reference is required.',
            'order_number.not_in'    => 'First create a bill book'
        ];

        foreach ($this->rows as $index => $row) {

            $messages["rows.$index.product_id.required"]    = "Product is required.";
            $messages["rows.$index.product_id.exists"]      = "Selected product is invalid.";

            $messages["rows.$index.unit_price.required"]    = "Unit price is required.";
            $messages["rows.$index.unit_price.numeric"]     = "Unit price must be a number.";
        }

        return $messages;
    }



    public function mount(){
        $this->salesmen = User::where([
            ['user_type',0],
            ['designation',2]
        ])->get();
        $this->salesman = auth()->guard('admin')->user()->id;
        $this->bill_book = Helper::generateInvoiceBill($this->salesman);
        $this->order_number = $this->bill_book['number'] ?? '';
        $this->products = Product::whereNull('deleted_at')->get();
        $this->invoice_date = Carbon::now()->format('Y-m-d');
        $this->due_date = Carbon::now()->format('Y-m-d');
        $this->rows = [
            [
                'product_id' => '',
                'quantity' => 1,
                'unit_price' => '',
                'total' => '',
                'products' => [],
            ],
        ];
    }

    public function resetForm()
    {
        $this->customer_name = '';
        $this->invoice_date = Carbon::now()->format('Y-m-d');
        $this->due_date = '';
        $this->source = '';
        $this->reference = '';
        $this->due_amount = 0;
        $this->totalAmount = 0;
        $this->totalInWords = '';

        $this->rows = [
            [
                'product_id' => '',
                'quantity' => 1,
                'unit_price' => '',
                'total' => '',
            ],
        ];
        $this->showPaymentReceipt = false;
        $this->payment_date = null;
        $this->payment_mode = null;
        $this->chq_utr_no = null;
        $this->bank_name = null;
        $this->actual_amount = 0;
        $this->amount = 0;
    }

    public function FindCustomer($search){
        $this->searchTerm = $search;
        if(!empty($this->searchTerm)){
            $users = User::where('user_type',1)->where('status',1)->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
            })
            ->take(20)
            ->get();

            $orderCustomers = Order::where('order_number', 'like', '%' . $this->searchTerm . '%')
            ->with('customer')
            ->take(5)
            ->get()
            ->pluck('customer')
            ->filter();

            $this->customers = $users->merge($orderCustomers)
            ->unique('id')
            ->values();
        }


    }

    public function selectCustomer($customerId)
    {
        $customer = User::find($customerId);
        if($customer) {
            $this->searchTerm = $customer->name;
            $this->customer_name = $customer->name;
            $this->selectedCustomerId = $customerId;
            $this->customers = []; 
        }
    }




    
    public function printInvoice()
    {
        $this->validate();

        // Calculate amounts
        $subtotal = collect($this->rows)->sum(fn($row) => floatval($row['total']));
        $tvaPercentage = floatval(env('TVA_PERCENTAGE', 18));
        $caPercentage = floatval(env('CA_PERCENTAGE', 5));
        $tva = $subtotal * ($tvaPercentage / 100);
        $ca = $tva * ($caPercentage / 100);
        $ht_amount = $subtotal - ($tva + $ca);
        
        // Update component properties
        $this->due_amount = $subtotal;
        $this->actual_amount = $subtotal;
        $this->ht_amount = $ht_amount;
        $this->tva_amount = $tva;
        $this->ca_amount = $ca;

        // Show payment receipt section
        $this->showPaymentReceipt = true;

        // Trigger print
        $this->dispatch('triggerPrint');
    }

    public function savePayment(){
        // dd($this->all());
        $this->validate([
            // 'payment_date' => 'required|date',
            'payment_mode' => 'required|in:cheque,neft,cash',
            'chq_utr_no' => 'required_if:payment_mode,cheque,neft',
            'bank_name' => 'required_if:payment_mode,cheque,neft',
            'amount' => [
            'nullable',
            'numeric',
            'min:0',
                function ($attribute, $value, $fail) {
                    if ($value > $this->actual_amount) {
                        $fail("Paid amount cannot exceed ".number_format($this->actual_amount, 2).' FCFA');
                    }
                },
            ]
        ],[
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
        
            'payment_mode.required' => 'Please select a mode of payment.',
            'payment_mode.in' => 'Selected payment mode is invalid.',
            
            'chq_utr_no.required_if' => 'Cheque/UTR number is required for Cheque or NEFT payments.',
            'bank_name.required_if' => 'Bank name is required for Cheque or NEFT payments.',
        
            // 'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.',
            'amount.max' => 'Amount cannot be greater than the actual amount.',
        ]);

        $subtotal = collect($this->rows)->sum(fn($row) => floatval($row['total']));
        $tvaPercentage = floatval(env('TVA_PERCENTAGE', 18));
        $caPercentage = floatval(env('CA_PERCENTAGE', 5));
        $tva = $subtotal * ($tvaPercentage / 100);
        $ca = $tva * ($caPercentage / 100);
        $ht_amount = $subtotal - ($tva + $ca);
        
        // Update component properties
        $this->due_amount = $subtotal;
        $this->actual_amount = $subtotal;
        $this->ht_amount = $ht_amount;
        $this->tva_amount = $tva;
        $this->ca_amount = $ca;
        // $this->payment_date = $this->invoice_date;
        // Show payment receipt section
        $this->showPaymentReceipt = true;


        DB::beginTransaction();

        try{
            // $this->validate();
            if($this->selectedCustomerId){
                $this->customer_id = $this->selectedCustomerId;

            }else{
                $user = User::create([
                    'user_type' => 1,
                    'name'     => $this->customer_name
                ]);
                $this->customer_id = $user->id;
            }
            $order = Order::create([
                'customer_id' => $this->customer_id,
                'created_by' => $this->salesman,
                'order_number' => $this->order_number,
                'customer_name' => $this->customer_name,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'source' => $this->source,
                'reference' => $this->reference,
                'total_amount' => $this->due_amount,
                'ht_amount' => $this->ht_amount,
                'tva_amount' => $this->tva_amount,
                'ca_amount' => $this->ca_amount,
                'paid_amount' => $this->amount,
                'due_amount' => $this->due_amount - $this->amount,
                'invoice_type' => 'manual',
                'status' => ($this->amount >= $this->due_amount) ? 'Fully Approved By Admin' : 'Approval Pending from TL',
            ]);

            // Create Order Items
            foreach ($this->rows as $row) {
                $product = $this->products->firstWhere('id',$row['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'piece_price' => $row['unit_price'],
                    'total_price' => $row['total'],
                    'product_name'=> $product->name
                ]);
            }
            
            $this->updateOrder($order->id);
            $this->updateOrderItems($order->id);
            $this->createPackingSlip($order->id);
            
            // Payment Receipt
            $this->voucher_no = 'PAYRECEIPT'.time();            
            $this->payment_date = $this->invoice_date;
            $this->staff_id = $this->salesman;
            // Store in Invoice and InvoicePayment
            $this->accountingRepository->StorePaymentReceipt($this->all());

       
            DB::commit();
            $this->resetForm();
            return redirect()->route('admin.order.index')->with('message','Manual Order Generated Successfully');
            // session()->flash('message', 'Invoice and payment saved successfully.');
        }catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            session()->flash('error', 'Error saving invoice: ' . $e->getMessage());
        }

    }

    public function updateOrder($order_id)
    {
        $order = Order::find($order_id); 
        
        if ($order) {
            $order->update([
                'customer_id' => $order->customer_id,
                'created_by' => $order->created_by,
                'status' => "Fully Approved By Admin",
            ]);
        }
    }

    public function updateOrderItems($order_id)
    {
            $subtotal = 0;
            $OrderItem =OrderItem::where('order_id', $order_id)->get();
            foreach ($OrderItem as $item) {
                $piecePrice = (float)$item['piece_price'];
                $quantity = (int)$item['quantity'];
                $totalPrice = $piecePrice * $quantity;

                OrderItem::where('id', $item['id'])->update([
                    'total_price' => $totalPrice,
                    'quantity' => $quantity,
                    'piece_price' => $piecePrice,
                ]);

                $subtotal += $totalPrice;
            }

            // Get the Order's air_mail
            $order = Order::find($order_id);
            $air_mail = $order->air_mail ?? 0;
            $total_amount = $subtotal + $air_mail;

            // Update the Order's total_amount
            $order->update(['total_amount' => $total_amount]);
    }
    public function createPackingSlip($order_id){
        $order = Order::find($order_id);
       
        if ($order) {
            // Calculate the remaining amount
            $packingSlip=PackingSlip::create([
                'order_id' => $order_id,
                'customer_id' => $order->customer_id,
                'slipno' => $order->order_number, 
                // 'is_disbursed' => ($remaining_amount == 0) ? 1 : 0,
                'is_disbursed' => 0,
                'created_by' => $order->created_by,
                'created_at' => now(),
                'disbursed_by' => $order->created_by,
                // 'updated_by' => auth()->id(),
                // 'updated_at' => now(),
            ]);

            
            do {
                $lastInvoice = Invoice::orderBy('id', 'DESC')->first();
                $invoice_no = str_pad(optional($lastInvoice)->id + 1, 6, '0', STR_PAD_LEFT);
            } while (Invoice::where('invoice_no', $invoice_no)->exists()); // Ensure unique invoice_no
            

            // $order->invoice_type = $this->document_type;
            $invoice = Invoice::create([
                'order_id' => $order_id,
                'customer_id' => $order->customer_id,
                'user_id' => $order->created_by,
                'packingslip_id' => $packingSlip->id,
                'invoice_no' => $invoice_no,
                'net_price' => $order->total_amount ?? null,
                'required_payment_amount' =>$order->total_amount ?? null,
                'created_by' =>  $order->created_by,
                'created_at' => now(),
                // 'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            // Fetch Products from Order Items
            $orderItems = $order->items;
             // Insert Invoice Products
             foreach ($orderItems as $key => $item) {
                InvoiceProduct::create([
                    'invoice_id' =>  $invoice->id,
                    'order_item_id'=>$item->id,
                    'product_id' => $item->product_id,
                    'product_name'=> $item->product? $item->product->name : "",
                    'quantity' => $item->quantity,
                    'single_product_price'=> $item->piece_price,
                    'total_price' => $item->total_price + ($item->air_mail ?? 0),
                    'is_store_address_outstation' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
             }

            Ledger::insert([
                'user_type' => 'customer',
                'transaction_id' => $invoice_no,
                'customer_id' => $order->customer_id,
                'transaction_amount' => $order->total_amount ?? null,
                'bank_cash' => 'cash',
                'is_credit' => 0,
                'is_debit' => 1,
                'entry_date' => date('Y-m-d H:i:s'),
                'purpose' => 'invoice',
                'purpose_description' => 'invoice raised of sales order for customer',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }  
    }
    





    public function ChangePaymentMode($mode){
        if($mode == "cash"){
            $this->chq_utr_no = null;
            $this->bank_name  = null;
            $this->showPaymentFields = false;
        }else{
            $this->showPaymentFields = true;
        }
    }

    public function getPreviewInvoiceNoProperty()
    {
        $year = date('Y');

        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $lastNumber = $lastInvoice 
            ? (int)substr($lastInvoice->invoice_no, strrpos($lastInvoice->invoice_no, '/') + 1)
            : 0;

        $nextNumber = $lastNumber + 1;

        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }



  

    public function addRow(){
        $this->rows[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => '',
            'total' => '',
            'products' => [],
        ];
    }

    // public function updatePrice($index)
    // {
    //     $quantity = (int)($this->rows[$index]['quantity'] ?? 1);
    //     $unitPrice = is_numeric($this->rows[$index]['unit_price']) ? (float) $this->rows[$index]['unit_price'] : 0;

    //     $this->rows[$index]['total'] = $quantity * $unitPrice;
    //     $this->calculateTotal();
    // }

    public function updatePrice($index)
    {
        $quantity = (int)($this->rows[$index]['quantity'] ?? 1);
        $quantity = $quantity > 0 ? $quantity : 1;

        $unitPrice = is_numeric($this->rows[$index]['unit_price']) 
                        ? (float) $this->rows[$index]['unit_price'] 
                        : 0;

        $this->rows[$index]['total'] = $quantity * $unitPrice;
        $this->calculateTotal();
    }


    public function updateQuantity($index, $type)
    {
        if ($type === 'increase') {
            $this->rows[$index]['quantity']++;
        } elseif ($type === 'decrease') {
            if ($this->rows[$index]['quantity'] > 1) {
                $this->rows[$index]['quantity']--;
            }
        }

        // Recalculate total
        $quantity = (int)$this->rows[$index]['quantity'];
        $unitPrice = isset($this->rows[$index]['unit_price']) && is_numeric($this->rows[$index]['unit_price'])
                        ? (float) $this->rows[$index]['unit_price']
                        : 0;

        $this->rows[$index]['total'] = $quantity * $unitPrice;
        $this->calculateTotal(); 
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // Re-index the array
    }

    
   


    public function updatedRows()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        // $this->totalAmount = collect($this->rows)->sum('total');
        $this->totalAmount = collect($this->rows)
        ->pluck('total')
        ->filter(fn($val) => is_numeric($val)) // only keep numeric values
        ->sum();
        // $this->totalInWords = $this->convertNumberToWords($this->totalAmount);
    }

    // public function convertNumberToWords($number)
    // {
    //     $numberToWords = new NumberToWords();
    //     $numberTransformer = $numberToWords->getNumberTransformer('en');
    
    //     return ucfirst($numberTransformer->toWords($number)) . ' only';
    // }

    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    public function render()
    {
        return view('livewire.order.add-invoice');
    }
}
