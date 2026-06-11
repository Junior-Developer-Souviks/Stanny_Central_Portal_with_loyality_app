<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use App\Models\{PurchaseOrder,Supplier,Fabric,StockFabric,Stock,PurchaseOrderProduct,FabricCategory};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\OpeningStockImport;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;



class PurchaseOrderIndex extends Component
{
    use WithFileUploads;

    public $purchaseOrders = '';
    public $search = '';
    public $suppliers,$bulkSupplier,$bulkFile;
    protected $paginationTheme = 'bootstrap'; // Optional: For Bootstrap styling

    protected $rules = [
        'bulkSupplier' => 'required|exists:suppliers,id',
        'bulkFile' => 'required|file|mimes:csv,xlsx,xls'
    ];

    public function mount(){
        $this->suppliers = Supplier::where('status',1)->where('deleted_at',NULL)->get();
    }

    public function bulkUploadOpeningStock()
   {
    $this->validate();

    DB::beginTransaction();

    try {
        $fileData = Excel::toArray(new OpeningStockImport, $this->bulkFile);
        $rows = $fileData[0];

        $supplier = Supplier::findOrFail($this->bulkSupplier);

        $purchaseOrder = PurchaseOrder::create([
            'supplier_id'   => $supplier->id,
            'unique_id'     => 'PO' . time(),
            'goods_in_type' => 'opening_stock',
            'is_approved'   => 1,
            'status'        => 1,
            'created_by'    => 1,
            'total_price'   => 0,
            'address'       => $supplier->billing_address,
            'city'          => $supplier->billing_city,
            'pin'           => $supplier->billing_pin,
            'state'         => $supplier->billing_state,
            'country'       => $supplier->billing_country,
            'landmark'      => $supplier->billing_landmark,
        ]);

        $fabricIds = [];

        foreach ($rows as $index => $row) {
            // if ($index === 0) continue; // Skip the header row

            $rowNumber = $index + 1;

            // Normalize column names
            $row = array_combine(
                array_map(fn($key) => strtolower(str_replace([" ", "'", ".", "`"], "", trim($key))), array_keys($row)),
                array_map('trim', $row)
            );

            $style  = $row['style'] ?? '';
            $title  = $row['radheys_ref_no'] ?? '';
            $pseudo = $row['ref_number_company'] ?? '';
            $qty    = floatval($row['closing_stk'] ?? 0);
            
            // Duplicate validation
            $key = strtolower(trim($title)) . '_' . strtolower(trim($pseudo));
        
            if (in_array($key, $duplicateCheck)) {
                throw new \Exception("Duplicate fabric found in CSV at row $rowNumber : $title ($pseudo)");
            }
        
            $duplicateCheck[] = $key;

            if (!$title || !$pseudo) {
                throw new \Exception("Missing fabric title or pseudo name at row $rowNumber");
            }

            // Fetch/Create category
            $category = FabricCategory::firstOrCreate(
                ['title' => $style],
                ['status' => 1]
            );

            // Fetch/Create fabric
            $fabric = Fabric::firstOrCreate(
                ['title' => $title, 'pseudo_name' => $pseudo],
                ['fabric_category_id' => $category->id, 'status' => 1, 'collection_id'      => 1,]
            );

            // Insert into purchase_order_products
            PurchaseOrderProduct::create([
                'purchase_order_id'    => $purchaseOrder->id,
                'collection_id'        => 1,
                'fabric_id'            => $fabric->id,
                'fabric_name'          => $fabric->title,
                'stock_type'           => 'fabric',
                'qty_in_meter'         => $qty,
                'qty_while_grn_fabric' => $qty,
                'piece_price'          => 0,
                'total_price'          => 0,
            ]);

            $fabricIds[] = $fabric->id;
        }

        // Create Stock entry
        $grn_no = "GRN-" . Helper::generateUniqueNumber();
        $stock = Stock::create([
            'grn_no'            => $grn_no,
            'purchase_order_id' => $purchaseOrder->id,
            'po_unique_id'      => $purchaseOrder->unique_id,
            'goods_in_type'     => 'opening_stock',
            'fabric_ids'        => json_encode(array_unique($fabricIds)),
            'total_price'       => 0,
        ]);

        // Insert into stock_fabrics
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $row = array_combine(
                array_map(fn($key) => strtolower(str_replace([" ", "'", ".", "`"], "", trim($key))), array_keys($row)),
                array_map('trim', $row)
            );

            $title  = $row['radheys_ref_no'] ?? '';
            $pseudo = $row['ref_number_company'] ?? '';
            $style  = $row['style'] ?? '';
            $qty    = floatval($row['closing_stk'] ?? 0);

            $category = FabricCategory::firstOrCreate(['title' => $style], ['status' => 1]);
            $fabric = Fabric::firstOrCreate(
                ['title' => $title, 'pseudo_name' => $pseudo],
                ['fabric_category_id' => $category->id, 'status' => 1]
            );

            StockFabric::create([
                'stock_id'      => $stock->id,
                'fabric_id'     => $fabric->id,
                'qty_in_meter'  => $qty,
                'qty_while_grn' => $qty,
                'piece_price'   => 0,
                'total_price'   => 0,
            ]);
        }

        $purchaseOrder->update([
            'fabric_ids'  => json_encode(array_unique($fabricIds)),
            'total_price' => 0,
        ]);

        DB::commit();

        session()->flash('message', "Bulk opening stock successfully uploaded!");
        $this->reset(['bulkSupplier', 'bulkFile']);
        $this->dispatch('closeModal');
    
    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('error', 'Bulk Upload Failed: ' . $e->getMessage());
    }
}

     public function downloadCsv(): BinaryFileResponse
    {
        $filePath = public_path('assets/csv/opening_stock.csv');
    
        return response()->download($filePath);
    }




    public function approveConfirmOrder($id)
    {
        $this->dispatch('confirmApprove', ['purchaseOrderId' => $id]); 
    }

    public function approveOrder($purchseOrderId)
    {
        $po = PurchaseOrder::findOrFail($purchseOrderId);
        $po->is_approved = 1;
        $po->save();
        session()->flash('message', 'Purchase Order approved successfully.');
    }
      public function updatingSearch()
    {
        $this->resetPage(); 
    }
    public function FindCustomer($keywords){
        $this->search = $keywords;
    }

    public function resetForm(){
        $this->reset(['search']);
    }
   
    public function downloadPdf($purchase_order_id)
    {
        
        $purchaseOrder = PurchaseOrder::with('supplier', 'orderproducts')->findOrFail($purchase_order_id);
        // Generate PDF
        $pdf =  Pdf::loadView('livewire.purchase-order.generate-pdf', compact('purchaseOrder'));
        $pdf->setPaper('A4', 'portrait');
        // Download the PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'purchase_order_' . $purchase_order_id . '.pdf');
    }
    public function render()
    {
        $query = PurchaseOrder::with(['orderproducts.product', 'orderproducts.fabric', 'orderproducts.collection'])
        ->when(!empty($this->search), function ($query) {
            $query->where('unique_id', 'like', '%' . $this->search . '%')
                ->orWhereHas('supplier', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
        })
        ->orderBy('id','DESC')
        ->paginate(20);

        return view('livewire.purchase-order.purchase-order-index', [
            'data' => $query,
        ]);
    }
    
}
