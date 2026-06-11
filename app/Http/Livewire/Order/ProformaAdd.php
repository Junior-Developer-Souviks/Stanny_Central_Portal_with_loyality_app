<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Product;
use App\Models\Country;
use App\Models\ProformaCustomer;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use Illuminate\Support\Facades\DB;


class ProformaAdd extends Component
{
    public $customer_name, $address, $email, $mobile,$condition,$date,$proforma_number;
    public $rows = [];
    public $products = [];
    public $product_id;
    public $quantity;
    public $unit_price;
    public $total;
    public $countries = [];
    public $selectedCountryId = null;
    public $country_code = '';
    public $selectedCountryPhone, $mobileLengthPhone;

    public function mount()
    {
        $this->products = Product::all();
        $this->countries = Country::where('status', 1)->get();
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

    public function rules(){
        return [
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'mobile' => [
                'required',
                'regex:/^\d{'. $this->mobileLengthPhone .'}$/',
            ],
            'condition' => 'required',
            'date' => 'required|date',
            'proforma_number' => ['required', 'regex:/^[0-9\-]+$/'],

            'rows.*.product_id' => 'required|exists:products,id',
            'rows.*.quantity' => 'required|integer|min:1',
            'rows.*.unit_price' => 'required|numeric|min:0',
            'rows.*.total' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'Customer name is required.',
            'customer_name.string' => 'Customer name must be a valid string.',
            'customer_name.max' => 'Customer name cannot exceed 255 characters.',

            'address.required' => 'Address is required.',
            'address.string' => 'Address must be a valid string.',
            'address.max' => 'Address cannot exceed 500 characters.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',

            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Mobile number must be exactly '.$this->mobileLengthPhone.' digits.',
            'condition.required' => 'Condition is required.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'proforma_number.required' => 'Proforma number is required.',

            'rows.*.product_id.required' => 'Product is required.',
            'rows.*.product_id.exists' => 'Selected product is invalid.',

            'rows.*.quantity.required' => 'Quantity is required.',
            'rows.*.quantity.integer' => 'Quantity must be an integer.',
            'rows.*.quantity.min' => 'Quantity must be at least 1.',

            'rows.*.unit_price.required' => 'Unit price is required.',
            'rows.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'rows.*.unit_price.min' => 'Unit price must be at least 0.',

            'rows.*.total.required' => 'Total amount is required.',
            'rows.*.total.numeric' => 'Total amount must be a valid number.',
            'rows.*.total.min' => 'Total amount must be at least 0.',
        ];
    }


    public function generateProforma(){
        // dd($this->all());
        $this->validate();
        try{
            DB::beginTransaction();
            // 1. Create Customer
            $customer = ProformaCustomer::create([
                'name' => $this->customer_name,
                'address' => $this->address,
                'email' => $this->email,
                'country_code' => $this->selectedCountryPhone,
                'mobile' => $this->mobile,
            ]);

            // 2. Calculate Totals
            $subtotal = collect($this->rows)->sum('total');
            $tvaPercentage = floatval(env('TVA_PERCENTAGE', 18));
            $caPercentage = floatval(env('CA_PERCENTAGE', 5));
            $tva = $subtotal * ($tvaPercentage / 100);
            $ca = $tva * ($caPercentage / 100);
            $ht_amount = $subtotal - ($tva + $ca);

            // 3. Create Proforma Invoice
            $proformaInvoice = ProformaInvoice::create([
                'customer_id' => $customer->id,
                'proforma_number' => $this->proforma_number,
                'date' => $this->date,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'conditions' => $this->condition,
            ]);

            foreach ($this->rows as $row) {
                ProformaInvoiceItem::create([
                    'proforma_id' => $proformaInvoice->id,
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'total_price' => $row['total'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.order.proformas.index')->with('success', 'Proforma invoice created successfully.');

        }catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            session()->flash('error', 'Error creating proforma: ' . $e->getMessage());
        }
    }

    public function GetCountryDetails($countryCode, $field){
        if($field === 'phone'){
            $country = Country::where('country_code', $countryCode)->first();
            if($country){
                $this->mobileLengthPhone = $country->mobile_length;
            }
        }
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
        
    }
    public function addRow()
    {
        $this->rows[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => '',
            'total' => '',
            'products' => [],
        ];
    }
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


    public function render()
    {
        return view('livewire.order.proforma-add');
    }
}
