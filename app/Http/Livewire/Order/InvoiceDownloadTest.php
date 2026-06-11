<?php

namespace App\Http\Livewire;

use Barryvdh\DomPDF\Facade as PDF;
use Livewire\Component;
use App\Models\Invoice;

class InvoiceDownload extends Component
{
    public $invoiceId;

    // Render method to load the view
    public function render()
    {
        return view('livewire.invoice-download');
    }

    // Method to handle PDF download
    public function downloadInvoice()
    {
        $invoice = Invoice::with(['order', 'customer', 'user', 'packing'])->findOrFail($this->invoiceId);

        // Generate PDF
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));

        // Download the PDF
        return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');
    }
}
