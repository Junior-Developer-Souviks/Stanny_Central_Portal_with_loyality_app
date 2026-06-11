<?php

namespace App\Http\Livewire\Expense;

use Livewire\Component;
use App\Models\Expense;
use Illuminate\Support\Str;

class ExpenseIndex extends Component
{
    public $parent_id;
    public $expenseId; // To track the expense being edited
    public $title;
    public $description;
    public $for_debit = false;
    public $for_credit = false;
    public $for_staff = false;
    public $for_customer = false;
    public $for_supplier = false;
    public $search;
    protected $updatesQueryString = ['search'];

    public function mount($parent_id,$expenseId = null){
        $this->parent_id = $parent_id;
        if($expenseId){
            $expense = Expense::find($expenseId);
            $this->expenseId = $expense->id;
            $this->for_debit = $expense->for_debit;
            $this->for_credit = $expense->for_credit;
            $this->for_staff = $expense->for_staff;
            $this->for_customer = $expense->for_customer;
            $this->for_supplier = $expense->for_supplier;
        }
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'for_debit' => 'boolean',
        'for_credit' => 'boolean',
        'for_staff' => 'boolean',
        'for_customer' => 'boolean',
        'for_supplier' => 'boolean',
    ];

    public function saveExpense(){
        $data = $this->validate();

         // Generate or update the slug
         $data['slug'] = Str::slug($this->title);

        if ($this->expenseId) {
            // Update logic
            $expense = Expense::find($this->expenseId);
            if ($expense) {
                $expense->update($data);
                session()->flash('message', 'Expense updated successfully.');
            }
        } else {
            // Create logic
            Expense::create(array_merge($data, ['parent_id' => $this->parent_id]));
            session()->flash('message', 'Expense created successfully.');
        }

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->expenseId = null;
        $this->title = '';
        $this->description = '';
        $this->for_debit = false;
        $this->for_credit = false;
        $this->for_staff = false;
        $this->for_customer = false;
        $this->for_supplier = false;
    }
    

    public function edit($id)
    {
        $expense = Expense::find($id);
        if ($expense) {
            $this->expenseId = $expense->id;
            $this->title = $expense->title;
            $this->description = $expense->description;
            $this->for_debit = (bool)$expense->for_debit;
            $this->for_credit = (bool)$expense->for_credit;
            $this->for_staff = (bool)$expense->for_staff;
            $this->for_customer = (bool)$expense->for_customer;
            $this->for_supplier = (bool)$expense->for_supplier;
        }
    }

    public function toggleStatus($id){
        $expense = Expense::find($id);
        $expense->status = !$expense->status;
        $expense->save();
        session()->flash('message','Expense Status has been updated');
    }

    public function render()
    {
        $expenses = Expense::where('parent_id', $this->parent_id)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->get();
        return view('livewire.expense.expense-index',compact('expenses'));
    }   
}
