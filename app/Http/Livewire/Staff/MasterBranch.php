<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use App\Models\Country;

class MasterBranch extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap'; 

    public $branchId;
    public $name, $email, $mobile, $whatsapp, $city, $address;
    public $search = '';
    public $country_id;
    public $countries = [];

    protected $rules = [
        'country_id' => 'required',
        'name' => 'required|unique:branches,name',
        'email'=> 'required|email|unique:branches,email',
        'mobile' => 'required|numeric|unique:branches,mobile',
        'whatsapp' => 'required|numeric|unique:branches,whatsapp',
        'city' => 'required',
        'address' => 'required',
    ];

    protected $messages = [
        'country_id.required' => 'Please select a country.',
        'name.required' => 'Branch name is required.',
        'name.unique' => 'This branch name already exists.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already used.',
        'mobile.required' => 'Mobile number is required.',
        'mobile.numeric' => 'Mobile number must be numeric.',
        'mobile.unique' => 'This mobile number is already used.',
        'whatsapp.required' => 'WhatsApp number is required.',
        'whatsapp.numeric' => 'WhatsApp number must be numeric.',
        'whatsapp.unique' => 'This WhatsApp number is already used.',
        'city.required' => 'City is required.',
        'address.required' => 'Address is required.',
    ];

    public function mount()
    {
        $this->countries = Country::orderBy('title')->where('status',1)->get();
    }


    public function FindBranch($keywords){
        $this->search = $keywords;
    }
    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination on search
    }

    public function resetFields()
    {
        $this->search = '';
        $this->country_id = null;
        $this->branchId = null;
        $this->name = '';
        $this->email = '';
        $this->mobile = '';
        $this->whatsapp = '';
        $this->city = '';
        $this->address = '';
    }

    public function storeBranch()
    {      
        $this->validate();     
        Branch::create([
            'country_id' => $this->country_id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'whatsapp' => $this->whatsapp,
            'city' => $this->city,
            'address' => $this->address,
        ]);

        session()->flash('message', 'Branch Created Successfully');
        $this->resetFields();
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $this->branchId = $id;
        $this->name = $branch->name;
        $this->email = $branch->email;
        $this->mobile = $branch->mobile;
        $this->whatsapp = $branch->whatsapp;
        $this->city = $branch->city;
        $this->address = $branch->address;
        $this->country_id = $branch->country_id; 
        $this->dispatch('refresh-chosen', country: $this->country_id);
    }

    public function updateBranch()
    {
        // dd($this->all());
        $this->validate([
            'country_id' => 'required',
            'name' => 'required|unique:branches,name,' . $this->branchId,
            'email'=> 'required|email|unique:branches,email,' . $this->branchId,
            'mobile' => 'required|numeric|unique:branches,mobile,' . $this->branchId,
            'whatsapp' => 'required|numeric|unique:branches,whatsapp,' . $this->branchId,
            'city' => 'required',
            'address' => 'required',
        ]);

        Branch::findOrFail($this->branchId)->update([
            'country_id' => $this->country_id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'whatsapp' => $this->whatsapp,
            'city' => $this->city,
            'address' => $this->address,
        ]);

        session()->flash('message', 'Branch Updated Successfully');
        $this->resetFields();
    }

    public function render()
    {
        $branchNames = Branch::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'desc')
            ->paginate(10);

        return view('livewire.staff.master-branch', compact('branchNames'));
    }
}
