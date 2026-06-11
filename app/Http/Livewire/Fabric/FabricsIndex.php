<?php

namespace App\Http\Livewire\Fabric;


use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Fabric;
use App\Models\Product;
use Illuminate\Http\Request;


class FabricsIndex extends Component
{
    use WithPagination;

    public $fabrics,$product;
    public  $title,$product_id, $status = 1, $fabricId,$image;
    public $search = '';
    protected $paginationTheme = 'bootstrap'; 


    public function mount($product_id)
    {
        $this->product = Product::with('fabrics')->findOrFail($product_id);
        $this->fabrics = $this->product->fabrics;
        // dd($this->fabrics);
    }
    // Render Method with Search and Pagination
    public function render()
    {
        $productFabrics = $this->product
                            ->fabrics()
                            ->where('title', 'like', "%{$this->search}%")
                            ->orderBy('id', 'desc')
                            ->paginate(10);
        return view('livewire.fabric.fabrics-index', compact('productFabrics'));
        
    }
}