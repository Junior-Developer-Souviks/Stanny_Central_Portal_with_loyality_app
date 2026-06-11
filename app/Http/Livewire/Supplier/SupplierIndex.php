<?php

namespace App\Http\Livewire\Supplier;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;


class SupplierIndex extends Component
{
    use WithPagination;
    
    public $suppliers;
    public $search;
    protected $updatesQueryString = ['search'];
    protected $paginationTheme = 'bootstrap'; 

    public function updatingSearch()
    {
        $this->resetPage(); 
    }
    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }

    public function toggleStatus($id){
        $supplier = Supplier::find($id);
        $supplier->status = !$supplier->status;
        $supplier->save();
        $this->suppliers = Supplier::all();
        session()->flash('success','Supplier status updated successfully');
    }
    public function FindSupplier($keywords){
        $this->search = $keywords;
    }
    public function resetForm(){
        $this->reset(['search']);
    }
    public function render()
    {
        $supplier_data = Supplier::where('deleted_at',NULL)
        ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })->paginate(10);
        return view('livewire.supplier.supplier-index',[
                'supplier_data' => $supplier_data
            ]);
    }

    public function deleteSupplier($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->delete();
            $this->suppliers = Supplier::all();  // Re-fetch suppliers after deletion
            session()->flash('success','Supplier deleted successfully');
        }
    }
}
