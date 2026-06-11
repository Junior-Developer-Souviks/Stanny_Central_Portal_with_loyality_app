<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\User;

class StaffView extends Component
{
    public $staff_id;

    public function mount($staff_id){
         // Fetch all users with their bank and address information
         $this->staff = User::with(['branch','bank','address','designationDetails'])->find($staff_id);
        //  dd($this->staff);
    }
    public function render()
    {
        return view('livewire.staff.staff-view',['staff'=>$this->staff]);
    }
}
