<?php

namespace App\Http\Livewire\CustomerLoyality;

use Livewire\Component;
use App\Models\CustomerPhotoUpload;
use App\Models\MarketingType;


class CustomerMarketingPhoto extends Component
{

    public $photoId;
    public $selectedMarketing = [];



    public function edit($id)
    {

        $this->photoId = $id;


        $photo = CustomerPhotoUpload::with('marketingTypes')
        ->find($id);


        $this->selectedMarketing =
        $photo->marketingTypes
        ->pluck('id')
        ->toArray();

    }



    public function update()
    {


        $photo = CustomerPhotoUpload::find($this->photoId);



        $photo->marketingTypes()
        ->sync($this->selectedMarketing);


        session()->flash(
            'message',
            'Marketing assigned successfully'
        );


        $this->resetForm();

    }



    public function resetForm()
    {
        $this->photoId = null;
        $this->selectedMarketing = [];
    }



    public function render()
    {


        return view(
            'livewire.customer-loyality.customer-marketing-photo',
            [

            'photos'=>CustomerPhotoUpload::with([
                'user',
                'marketingTypes'
            ])->latest()->paginate(10),


            'marketingTypes'=>MarketingType::where('status',1)
            ->get()

            ]
        );


    }


}