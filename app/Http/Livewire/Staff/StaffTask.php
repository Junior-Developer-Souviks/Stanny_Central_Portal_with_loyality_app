<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\User;

class StaffTask extends Component
{
    public $staff,$staff_id;

    public function mount($staff_id){
        $this->staff = User::find($staff_id);
    }
    public function render()
    {
        return view('livewire.staff.staff-task',['staff'=>$this->staff]);
    }
}
