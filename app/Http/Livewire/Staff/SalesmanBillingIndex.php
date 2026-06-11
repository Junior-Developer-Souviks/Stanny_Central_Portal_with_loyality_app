<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\User;
use App\Models\SalesmanBilling;

class SalesmanBillingIndex extends Component
{
    public $salesman_id;
    public $start_no;
    public $end_no;
    public $billing_id;
    public $numberLength;
    public $assign_new_salesman = false;
    public $staff_id;

    public $search = '';
    
  
    public function mount(){
        $staff_id = request()->query('staff_id');
        if($staff_id){
            $this->staff_id = $staff_id;
            $this->salesman_id = $staff_id;
        }
    }

    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }

    public function FindBillBook($keywords){
        $this->search = $keywords;
    }

    public function resetForm()
    {
        $this->search = '';
        $this->staff_id = null;
        $this->salesman_id = null;
        $this->start_no = null;
        $this->end_no = null;
        $this->billing_id = null;
        $this->numberLength = null;
        $this->assign_new_salesman = false;
    }


    public function assignToNewSalesman($billingId){
       $this->assign_new_salesman = true;
       $this->billing_id = $billingId;
       $existingBilling = SalesmanBilling::find($billingId);
       if ($existingBilling) {
          if($existingBilling->no_of_used == 0){
            $this->start_no = $existingBilling->start_no;
          }else{
            $this->start_no = ($existingBilling->start_no + $existingBilling->no_of_used) ;
          }
        //    $this->start_no = abs(($existingBilling->total_count - $existingBilling->no_of_used)-$existingBilling->end_no) + 1; 
           $this->end_no = $existingBilling->end_no; 
           $this->salesman_id = $existingBilling->salesman_id;
       }  
    }
    
    public function SubmitNewSalesman() {
        $this->validate([
            'salesman_id' => 'required|exists:users,id',
            'start_no' => 'required|numeric',
            'end_no' => 'required|numeric|gt:start_no',
        ]);
        
    
        $totalCount = ((int)$this->end_no - (int)$this->start_no)+1 ;
    
        // $usedCount = SalesmanBilling::whereBetween('start_no', [$this->start_no, $this->end_no])
        //     ->orWhereBetween('end_no', [$this->start_no, $this->end_no])
        //     ->where('no_of_used','>', 0)
        //     ->count();
    
        SalesmanBilling::create([
            'salesman_id' => $this->salesman_id,
            'start_no' => $this->start_no,
            'end_no' => $this->end_no,
            'total_count' => $totalCount,
            'no_of_used' => 0,
        ]);
    
       // Update the existing billing record
        $existingBilling = SalesmanBilling::find($this->billing_id);
        if ($existingBilling) {
            $updatedEndNo = $this->start_no -1 ;
            $updatedTotalCount = ( $updatedEndNo - $existingBilling->start_no)+1 ;

            // If the updated total count is zero or negative, delete the old record
            if($updatedTotalCount <= 0){
                $existingBilling->delete();
            }else{
                $existingBilling->update([
                    'end_no' => $updatedEndNo,
                    'total_count' => $updatedTotalCount,
                ]);
            }
        }
    
        // Reset fields and show success message
        $this->reset(['salesman_id', 'start_no', 'end_no']);
        session()->flash('message', 'Salesman billing number successfully assigned!');
    }
    

   

    public function submit(){
        $this->validate([
            'salesman_id' => [
                'required',
                'exists:users,id',
            ],
            'start_no' => [
                'required',
                function ($attribute, $value, $fail) {
                    $startNo = (int) $this->start_no; // Convert to integer
                    $endNo = (int) $this->end_no; // Convert to integer
    
                    $overlap = SalesmanBilling::where(function ($query) use ($startNo, $endNo) {
                        $query->whereBetween('start_no', [$startNo, $endNo])
                              ->orWhereBetween('end_no', [$startNo, $endNo])
                              ->orWhere(function ($query) use ($startNo, $endNo) {
                                  $query->where('start_no', '<=', $startNo)
                                        ->where('end_no', '>=', $endNo);
                              });
                    })->first();
    
                    if ($overlap) {
                        $salesmanName = $overlap->salesman ? $overlap->salesman->name : 'Unknown';
                        $fail("The start or end number overlaps with the existing range of salesman '{$salesmanName}' (Range: {$overlap->start_no} to {$overlap->end_no}).");
                    }
                },
            ],
            'end_no' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ((int)$value <= (int)$this->start_no) {
                        $fail('The end number must be greater than the start number.');
                    }
                },
            ],
        ]);
    
        // Ensure consistent number length by padding numbers
        $this->numberLength = strlen((string) $this->end_no);
        $normalizedStartNo = str_pad($this->start_no, $this->numberLength, '0', STR_PAD_LEFT);
        $normalizedEndNo = str_pad($this->end_no, $this->numberLength, '0', STR_PAD_LEFT);
        
        $totalCount = ((int)$this->end_no - (int)$this->start_no + 1);
    
        // Calculate used numbers count
        // $usedCount = SalesmanBilling::where(function ($query) use ($normalizedStartNo, $normalizedEndNo) {
        //     $query->whereBetween('start_no', [$normalizedStartNo, $normalizedEndNo])
        //           ->orWhereBetween('end_no', [$normalizedStartNo, $normalizedEndNo]);
        // })->where('no_of_used', '>', 0)->count();
    
        // Insert into SalesmanBilling
        SalesmanBilling::create([
            'salesman_id' => $this->salesman_id,
            'start_no' => $normalizedStartNo,
            'end_no' => $normalizedEndNo,
            'total_count' => $totalCount,
            // 'no_of_used' => $usedCount,
            'no_of_used' => 0,
        ]);
    
        $this->reset(['salesman_id', 'start_no', 'end_no']);
        session()->flash('message', 'Salesman billing number added successfully!');
        $this->resetForm();

    }

    public function edit($id){
        $billing = SalesmanBilling::findOrFail($id);
        $this->billing_id = $billing->id;
        $this->salesman_id = $billing->salesman_id;
        $this->start_no = $billing->start_no;
        $this->end_no = $billing->end_no;
    }

    public function update()
    {
         $this->validate([
        'salesman_id' => [
            'required',
            'exists:users,id', // Ensure the salesman exists in the users table
            function ($attribute, $value, $fail) {
                // Check if the selected salesman already has an active billing range (excluding current record)
                $exists = SalesmanBilling::where('salesman_id', $value)
                                         ->where('id', '!=', $this->billing_id) // Exclude the current record
                                         ->exists();
                if ($exists) {
                    $fail('A billing range already exists for the selected salesman.');
                }
            },
        ],
        'start_no' => [
            'required',
            function ($attribute, $value, $fail) {
                $overlap = SalesmanBilling::where(function ($query) {
                    // Check for overlap of start_no and end_no
                    $query->whereBetween('start_no', [$this->start_no, $this->end_no])
                          ->orWhereBetween('end_no', [$this->start_no, $this->end_no])
                          ->orWhere(function ($query) {
                              $query->where('start_no', '<=', $this->start_no)
                                    ->where('end_no', '>=', $this->end_no);
                          });
                })
                ->where('id', '!=', $this->billing_id) // Exclude the current record
                ->where('salesman_id', $this->salesman_id)
                ->first();

                if ($overlap) {
                    $salesmanName = $overlap->salesman ? $overlap->salesman->name : 'Unknown';
                    $fail("The start or end number overlaps with the existing range of salesman '{$salesmanName}' (Range: {$overlap->start_no} to {$overlap->end_no}).");
                }
            },
        ],
        'end_no' => [
            'required',
            function ($attribute, $value, $fail) {
                if ((int)$value <= (int)$this->start_no) {
                    $fail('The end number must be greater than the start number.');
                }
            },
        ],
    ]);
        $this->numberLength = strlen((string)$this->end_no);
        // Normalize the start and end numbers to match the length
        $normalizedStartNo = str_pad($this->start_no, $this->numberLength, '0', STR_PAD_LEFT);
        $normalizedEndNo = str_pad($this->end_no, $this->numberLength, '0', STR_PAD_LEFT);

        // Calculate the total_count
         $totalCount = ((int)$this->end_no - (int)$this->start_no) + 1;
        // Calculate no_of_used
       
        $usedCount = SalesmanBilling::whereBetween('start_no', [$this->start_no, $this->end_no])
                                        ->orWhereBetween('end_no', [$this->start_no, $this->end_no])
                                        ->where('no_of_used', '>', 0) 
                                        ->count();
        
        $billing = SalesmanBilling::findOrFail($this->billing_id);
        $billing->update([
            'salesman_id' => $this->salesman_id,
            'start_no' => $normalizedStartNo,
            'end_no' => $normalizedEndNo,
            'total_count' => $totalCount,
            'no_of_used' => $usedCount,
        ]);

        $this->reset(['salesman_id', 'start_no', 'end_no']);
        
        session()->flash('message', 'Salesman billing number updated successfully!');
        $this->resetForm(); // Reset all fields

    }

    public function destroy($id)
    {
        SalesmanBilling::findOrFail($id)->delete();
        session()->flash('message', 'Salesman billing number deleted successfully!');
    }


    public function render()
    {
        $salesman = User::where('user_type',0)->get();
        
        $billings = SalesmanBilling::with('salesman')
        ->when($this->staff_id, function ($query) {
            $query->where('salesman_id', $this->staff_id);
        })
        ->when($this->search, function ($query) {
            $query->whereHas('salesman', function ($subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        })
        ->orderBy('id', 'DESC')
        ->get();

        return view('livewire.staff.salesman-billing-index',['salesmans'=>$salesman, 'billings' => $billings,]);
    }
}
