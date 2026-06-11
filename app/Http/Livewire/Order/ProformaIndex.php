<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\ProformaInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithPagination;


class ProformaIndex extends Component
{
    use WithPagination;

    public $invoices = [];
    public $search = '';
    protected $paginationTheme = 'bootstrap'; 


    public function downloadProformaInvoice($proformaId)
    {
        $proforma = ProformaInvoice::with('customer', 'items.product')->findOrFail($proformaId);

          // Generate PDF
            $pdf = PDF::loadView('invoice.proforma_invoice_pdf', compact('proforma'));
        
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'proforma_' . $proforma->proforma_number . '.pdf');
      
    }

    public function FindCustomer($keywords){
        $this->search = $keywords;
    }
   
    
    public function render()
    {
        $invoice_data = ProformaInvoice::with('customer')
        ->where('proforma_number', 'like', '%' . $this->search . '%')
        ->orWhereHas('customer', function($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })
        ->paginate(20);
        return view('livewire.order.proforma-index',[
            'invoice_data' => $invoice_data
        ]);
    }
}
