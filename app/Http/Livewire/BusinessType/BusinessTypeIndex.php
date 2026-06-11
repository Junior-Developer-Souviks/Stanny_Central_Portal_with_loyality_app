<?php

namespace App\Http\Livewire\BusinessType;

use Livewire\Component;
use App\Models\BusinessType;

class BusinessTypeIndex extends Component
{
    public $businessTypeId;
    public $title;
    public $search = '';
    
    public function FindBusiness($keywords){
        $this->search = $keywords;
    }
    // public function resetForm(){
    //     $this->reset(['search']);
    // }
    protected $rules = [
        'title' => 'required',
    ];

    public function resetForm()
    {
        $this->search = '';
        $this->title = '';
        $this->businessTypeId = null;
    }

    public function storeBusinessType()
    {
        $this->validate();

        BusinessType::create([
            'title' => $this->title,
        ]);

        session()->flash('message', 'Business Type Created Successfully');
        $this->resetForm();
    }

    public function edit($id)
    {
        $business_type = BusinessType::findOrFail($id);
        $this->businessTypeId = $business_type->id;
        $this->title = $business_type->title;
    }

    public function updateBusinessType()
    {
        $this->validate();

        BusinessType::where('id', $this->businessTypeId)->update([
            'title' => $this->title,
        ]);

        session()->flash('message', 'Business Type Updated Successfully');
        $this->resetForm();
    }

    public function render()
    {
        $business_types = BusinessType::where('title', 'like', '%' . $this->search . '%')->get();

        return view('livewire.business-type.business-type-index', compact('business_types'));
    }
}
