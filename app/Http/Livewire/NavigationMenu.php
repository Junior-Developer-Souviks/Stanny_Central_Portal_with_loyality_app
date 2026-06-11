<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Permission;
use App\Models\DesignationPermission;
use Illuminate\Support\Facades\Auth;

class NavigationMenu extends Component
{
    public $modules = [];
    public $user;
    public function mount(){
        $this->user = Auth::guard('admin')->user();
         // Define the modules dynamically
         $this->modules = [
            [
                'name'=>'Dashboard',
                'route'=>['admin.dashboard'],
                'icon'=>'dashboard'
            ],
            [
                'name'=>'Customer Management',
                'route'=>['customers.index','admin.user-address-form','admin.customers.edit','admin.customers.details'],
                'icon'=>'group'
            ],
            [
                'name'=>'Supplier Management',
                'route'=>[
                           'suppliers.index',
                           'suppliers.add',
                           'suppliers.edit',
                           'suppliers.details'
                        ],
                'icon'=>'store'
            ]
         ];
    }
    public function hasPermissionByParent($parentName)
    {
        // Ensure designation is loaded
        if (!$this->user || !$this->user->designation) {
            return false;
        }
        $permission_id = Permission::where('parent_name', $parentName)->pluck('id');
        if($permission_id){
            return DesignationPermission::whereIn('permission_id', $permission_id)->where('designation_id', $this->user->designation)->exists();
        }else{
            return false;
        }
    }
    public function hasPermission($name)
    {
        if (!$this->user || !$this->user->designation) {
            return false;
        }

        $permission_id = Permission::where('name', $name)->value('id');

        if ($permission_id) {
            return DesignationPermission::where('permission_id', $permission_id)
                ->where('designation_id', $this->user->designation)
                ->exists();
        }

        return false;
    }

    public function render()
    {
        return view('livewire.navigation-menu');
    }
}
