<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\User;
use App\Models\Branch;
use App\Models\Designation;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;


class StaffIndex extends Component
{
    use WithPagination;

    public $staff,$user_id;
    public $search, $branch_name, $designation_name;
    public $branches, $designationList;
    public function mount(){
    // Fetch all users with their bank and address information
        // $this->staff = User::with(['bank','address','designationDetails'])->orderBy('name', 'ASC')->where('designation', '!=', 1)->where('user_type',0)->get();
        $this->branches = Branch::orderBy('name','ASC')->get();
        $this->designationList = Designation::orderBy('name','ASC')->get();

    }

     public function updatingSearch()
    {
        $this->resetPage(); 
    }

    public function toggleStatus($user_id){
        $staff = User::find($user_id);
        if($staff){
            $staff->status  = !$staff->status;
            $staff->save();
            session()->flash('message', 'Staff status updated successfully!');
        }

    }


    public function SelectBranch($value){
        $this->branch_name = $value;
    }

    public function SelectDesignation($value){
        $this->designation_name = $value;
    }

    // public

    public function FindCustomer($keywords){
        $this->search = $keywords;
    }

    

    public function resetForm()
    {
        $this->reset(['search','branch_name','designation_name']);
    }
    public function render()
    {
        $query = User::with(['branch','bank', 'address', 'designationDetails','teamLead'])
                ->where('designation', '!=', 1)
                ->where('user_type', 0)
                ->orderBy('name', 'ASC');

            $auth = Auth::guard('admin')->user();

            // Apply self + team filter only if not super admin
            if (!$auth->is_super_admin) {
                $query->where(function ($q) use ($auth) {
                    $q->where('id', $auth->id)
                    ->orWhere('parent_id', $auth->id);
                });
            }

            $query->orderBy('name', 'ASC');
            // Apply search filter (Search by name, email, phone)
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
                  
                  //  Search in branch name
                $q->orWhereHas('branch', function ($branchQuery) {
                    $branchQuery->where('name', 'like', '%' . $this->search . '%');
                });
        
                //  Search in team lead name
                $q->orWhereHas('teamLead', function ($teamQuery) {
                    $teamQuery->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('surname', 'like', '%' . $this->search . '%');
                });
            });
        }

        // dd($this->branch_name);
        if(!empty($this->branch_name)){
            $query->where('branch_id',$this->branch_name);
        }

        if(!empty($this->designation_name)){
            $query->where('designation',$this->designation_name);
        }

        $staff_data = $query->paginate(20);

        return view('livewire.staff.staff-index', [
            'staff_data' => $staff_data,
            'branches' => $this->branches,
            'designationList' => $this->designationList
        ]);
    }
}
