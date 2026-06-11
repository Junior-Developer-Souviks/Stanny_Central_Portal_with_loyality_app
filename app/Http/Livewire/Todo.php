<?php

namespace App\Http\Livewire;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use App\Helpers\Helper;

use Livewire\Component;

class Todo extends Component
{
    public $customer_id,$user_id,$todo_type,$todo_date,$remark,$customer,$searchResults = [],$staffs =[],$searchStaff,
    $staff_id,$start_date,$end_date,$search_staff_id;

    public function mount()
    {
    $this->staffs = User::where('user_type', 0)->whereIn('designation', [2,12])->select('name', 'id','designation')->orderBy('name', 'ASC')->get();

    }
        public function rules()
    {
        $rules['staff_id'] = 'required'; // Optional: adjust validation as needed
        $rules['todo_type'] = 'required'; // Optional: adjust validation as needed
        $rules['remark'] = 'required'; // Optional: adjust validation as needed
        $rules['todo_date'] = 'required'; // Optional: adjust validation as needed





    return $rules;
    }
    public function render()
    {
        $todolists=$this->fetchData();

        return view('livewire.todo',compact('todolists'));
    }
    public function submit()
    {
                    $this->validate();

        try {
              $admin_id = Auth::guard('admin')->user()->id;
            $todoData=[
            'user_id'=>$this->staff_id,
            'customer_id'=>$this->customer_id,
            'created_by'=>$admin_id,
            'todo_type'=>$this->todo_type,
            'todo_date'=>$this->todo_date,
            'remark'=>$this->remark
            ];

            TodoList::insertGetId($todoData);
            $this->ResetForm();
            session()->flash('success', 'ToDo List added successfully.');

            $this->fetchData();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

    }
    public function FindCustomer($term)
    {
        $this->searchResults = Helper::GetCustomerDetails($term);

    }
    public function selectCustomer($value)
    {
        $this->customer_id = $value;

    }
    public function fetchData()
    {
        //die($this->search_staff_id.'HHH');
        return TodoList::with(['customer:id,name','staff:id,name'])
        ->when($this->start_date, fn($query) => $query->whereDate('todo_date', '>=', $this->start_date)) // Start date filter
        ->when($this->end_date, fn($query) => $query->whereDate('todo_date', '<=', $this->end_date)) // End d
        ->when($this->search_staff_id, fn($query) => $query->where('user_id','=', $this->search_staff_id)) // End d

        ->orderBy('created_at', 'desc')->paginate(10);

    }
    public function ResetForm(){
        $this->reset(['customer','customer_id','staff_id','todo_type','todo_date','remark']);
        $this->searchResults=[];

    }
    public function resetSearch()
    {
      $this->reset(['start_date','end_date','search_staff_id']);
      $this->fetchData();
    }


    public function filterData()
    {
        $this->fetchData();
    }

}
