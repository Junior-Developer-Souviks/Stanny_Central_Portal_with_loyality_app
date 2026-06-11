<?php

// namespace App\Http\Livewire\PurchaseOrder;
// use Barryvdh\DomPDF\Facade\Pdf;
// use App\Models\PurchaseOrder;
// use Illuminate\Http\Response;
// use Livewire\Component;

// class GeneratePdf extends Component
// {
//     public $purchase_order_id;

//     public function mount($purchase_order_id){
//         $this->purchase_order_id = $purchase_order_id;
//     }

//     public function downloadPdf()
//     {
//         $purchaseOrder = PurchaseOrder::with('supplier','orderproducts')->findOrFail($this->purchase_order_id);
//         $pdf = Pdf::loadView('livewire.purchase-order.generate-pdf', compact('purchaseOrder'));
//         return response()->streamDownload(function () use ($pdf) {
//             echo $pdf->stream();
//         }, 'purchase_order_' . $this->purchase_order_id . '.pdf');
//     }


    // public function render()
    // {
    //     abort(404);
    // }
// }
