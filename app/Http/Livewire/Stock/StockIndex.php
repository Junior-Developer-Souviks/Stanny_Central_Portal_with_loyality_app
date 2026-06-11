<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;
use App\Models\StockProduct;
use App\Models\StockFabric;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockProductExport;
use Carbon\Carbon;
use App\Exports\StockFabricExport;


class StockIndex extends Component
{
    use WithPagination;

    public $activeTab = 'product'; // Default active tab
    protected $paginationTheme = 'bootstrap';
    public $startDateProduct;
    public $endDateProduct;
    public $startDateFabric;
    public $endDateFabric;
    public $searchProduct  = '';
    public $searchFabric  = '';
   
     public function updatingSearch()
    {
        $this->resetPage(); 
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function clearProductFilters(){
        $this->reset(['searchProduct','startDateProduct','endDateProduct']);
       
    }

    public function clearFabricFilters(){
        $this->reset(['searchFabric','startDateFabric','endDateFabric']);
    }
      

    public function exportStockProduct()
    {
        $fileName = 'product_stock_' . now()->format('Ymd_His') . '.csv';

        return Excel::download(new StockProductExport($this->searchProduct, $this->startDateProduct, $this->endDateProduct), $fileName);
    }


    
    public function exportStockFabric()
    {
        $fileName = 'fabric_stock_' . Carbon::now()->format('Ymd_His') . '.csv';
        return Excel::download(new StockFabricExport($this->searchFabric, $this->startDateFabric, $this->endDateFabric), $fileName);
    }
    public function resetForm(){
        $this->reset(['searchProduct','searchFabric', 'startDateProduct','endDateProduct','startDateFabric','endDateFabric']);
    }

    public function FindCustomer($keywords){
        $this->searchFabric = $keywords;
    }

    public function AddStartDate($date){
        $this->start_date = $date;
    }

    public function AddEndDate($date){
        $this->end_date = $date;
    }

    public function render()
    {

        $products = StockProduct::with('product')
        ->when($this->searchProduct, function ($q) {
            $q->whereHas('product', function ($products) {
                $products->where('name', 'like', '%' . $this->searchProduct . '%');
            });
        })
        ->when($this->startDateProduct, function ($q) {
            $q->whereDate('created_at', '>=', $this->startDateProduct);
        })
        ->when($this->endDateProduct, function ($q) {
            $q->whereDate('created_at', '<=', $this->endDateProduct);
        })
        ->paginate(10);
        

        $fabrics = StockFabric::with('fabric')
            ->when($this->searchFabric, function ($q) {
                $q->whereHas('fabric', function ($fabrics) {
                    $fabrics->where('title', 'like', '%' . $this->searchFabric . '%');
                });
            })
            ->when($this->startDateFabric, function ($q) {
                $q->whereDate('created_at', '>=', $this->startDateFabric);
            })
            ->when($this->endDateFabric, function ($q) {
                $q->whereDate('created_at', '<=', $this->endDateFabric);
            })
            ->paginate(10);

        return view('livewire.stock.stock-index', [
            'products' => $products,
            'fabrics' => $fabrics,
        ]);
    }

}
