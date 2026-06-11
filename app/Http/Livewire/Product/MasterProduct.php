<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Product;
use App\Models\Collection;
// use App\Models\Product;
use App\Models\Category;
// use App\Models\Collection;
use App\Models\Fabric;
// use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use App\Exports\SampleProductsExport;
use Livewire\WithPagination;

class MasterProduct extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $productData;
    public $collection;
    public $searchFilter;
    public $file;
    public $search;

    protected $paginationTheme = 'bootstrap'; 

    public function mount(){
        $this->collection = Collection::all();
    }
    
    public function updatingSearch()
    {
        $this->resetPage(); 
    }

    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }
    
    public function deleteProduct($product_id){
        $product = Product::findOrFail($product_id);
        if($product->product_image && \Storage::disk('public')->exists($product->product_image)){
            \Storage::disk('public')->delete($product->product_image);
        }
        $product->deleted_at = now(); 
        $product->save();
        session()->flash('message','Product deleted successfully.');
    }
    public function export()
    {
        return response()->streamDownload(function () {
            echo Excel::raw(new ProductsExport, \Maatwebsite\Excel\Excel::XLSX);
        }, 'products.csv');
    }

    public function sampleExport()
    {
        return response()->streamDownload(function () {
            echo Excel::raw(new SampleProductsExport, \Maatwebsite\Excel\Excel::XLSX);
        }, 'products.csv');
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        Excel::import(new ProductsImport, $this->file->getRealPath());

        session()->flash('message', 'Products imported successfully!');
    }


    public function toggleStatus($product_id){
        $product = Product::findOrFail($product_id);
        $product->status = !$product->status;
        $product->save();
        session()->flash('message', 'Product status updated successfully.');
    }
    public function FindProduct($keywords){
        $this->search = $keywords;
    }
    public function resetForm(){
        $this->reset(['search']);
    }

    public function downloadProductCSV()
    {
        $filePath = public_path('assets/csv/products.csv'); // Correct file path

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function render()
    {
        $query = Product::with('category','sub_category')->whereNull('deleted_at');
        // filter by selected collection
        if($this->searchFilter){
            $query->where('collection_id',$this->searchFilter);
        }
        
        $products = $query->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');// Apply search filter if provided
        })->latest()
        ->paginate(10);
        return view('livewire.product.master-product', compact('products'));
    }
}
