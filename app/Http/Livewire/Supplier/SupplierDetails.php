<?php

namespace App\Http\Livewire\Supplier;

use App\Models\Supplier;
use Livewire\Component;

class SupplierDetails extends Component
{
    public $supplier;
    public $existingGstFile;

    public function mount($id)
    {
        $this->supplier = Supplier::find($id);
        $this->existingGstFile = $this->supplier->gst_file;
    }

    public function render()
    {
        return view('livewire.supplier.supplier-details');
    }
}
