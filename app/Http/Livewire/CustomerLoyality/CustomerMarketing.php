<?php

namespace App\Http\Livewire\CustomerLoyality;

use Livewire\Component;
use App\Models\MarketingType;

class CustomerMarketing extends Component
{
    public $marketing_id;
    public $name;

    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];


    public function save()
    {
        $this->validate();


        MarketingType::updateOrCreate(
            [
                'id' => $this->marketing_id
            ],
            [
                'name' => $this->name,
            ]
        );


        session()->flash('message',
            $this->isEdit ? 'Marketing Type Updated' : 'Marketing Type Created'
        );


        $this->resetForm();
    }



    public function edit($id)
    {
        $data = MarketingType::findOrFail($id);

        $this->marketing_id = $data->id;
        $this->name = $data->name;

        $this->isEdit = true;
    }
    
    public function toggleStatus($id)
    {
        $data = MarketingType::find($id);
    
        $data->update([
            'status' => !$data->status
        ]);
        
        session()->flash(
            'message',
            'Marketing type status updated successfully'
        );
    }



    public function confirmDelete($id)
    {
        $this->dispatch('showDeleteConfirm', [
            'id' => $id
        ]);
    }
    
    
    
    
    
    public function delete($id)
    {
        MarketingType::findOrFail($id)->delete();
    
    
        session()->flash(
            'message',
            'Marketing type deleted successfully'
        );
    }



    public function resetForm()
    {
        $this->marketing_id = null;
        $this->name = '';
        $this->isEdit = false;
    }



    public function render()
    {
        return view('livewire.customer-loyality.customer-marketing',[
            'marketingTypes' => MarketingType::latest()->get()
        ]);
    }
}