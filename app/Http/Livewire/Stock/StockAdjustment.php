<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Imports\OpeningStockImport;
use App\Models\{StockAdjustmentLog,StockProduct,StockFabric};
use Livewire\WithPagination;

class StockAdjustment extends Component
{
    use WithFileUploads,WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    public $csvFile;
    // public $logData;
    protected $rules = [
        'csvFile' => 'required|file|mimes:csv'
    ];

   

    public function uploadStockAdjustment()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $file = $this->csvFile;
            $data = Excel::toArray(new OpeningStockImport, $file);

            if (empty($data) || empty($data[0])) {
                session()->flash('error', 'The uploaded file is empty or invalid.');
                DB::rollBack();
                return;
            }

            $processedCount = 0;
            $errorCount = 0;
            $errors = [];

            //  Group records by fabric/product (case & space insensitive)
            $rows = collect($data[0])
                ->filter(fn($row) => !empty(array_filter($row))) // remove empty rows
                ->groupBy(fn($row) => strtolower(trim(preg_replace('/\s+/', ' ', $row['fabricproduct'] ?? ''))))
                ->map(fn($group) => [
                    'fabricproduct' => trim(preg_replace('/\s+/', ' ', $group[0]['fabricproduct'])),
                    'total_adjustment' => $group->sum(fn($item) => floatval($item['stock'] ?? 0)),
                    'remarks' => $group[0]['remarks'] ?? 'Uploaded stock deduction',
                ]);

            foreach ($rows as $row) {
                $fabricOrProduct = $row['fabricproduct'];
                $adjustmentQty   = floatval($row['total_adjustment']);
                $remarks         = $row['remarks'];

                if (!$fabricOrProduct || $adjustmentQty <= 0) {
                    $errors[] = "Invalid Fabric/Product or Stock ({$fabricOrProduct}).";
                    $errorCount++;
                    continue;
                }

                /** ✴ Check Fabric Stock First */
                $fabricStock = StockFabric::whereHas('fabric', function ($q) use ($fabricOrProduct) {
                    $q->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($fabricOrProduct))]);
                })->first();

                if ($fabricStock) {
                    $oldQty = floatval($fabricStock->qty_in_meter);
                    $oldGrn = floatval($fabricStock->qty_while_grn);
                    $newQty = $oldQty - $adjustmentQty;
                    $newGrn = $oldGrn - $adjustmentQty;
                    
                    if ($newQty < 0) {
                        $errors[] = "Insufficient fabric stock '{$fabricOrProduct}'. Current: {$oldQty}, Deducting: {$adjustmentQty}";
                        $errorCount++;
                        continue;
                    }

                    $fabricStock->update([
                        'qty_in_meter' => $newQty,
                        'qty_while_grn' => $newGrn
                        ]);

                    StockAdjustmentLog::create([
                        'fabric_id'  => $fabricStock->fabric_id,
                        'product_id' => null,
                        'adjustment'   => -$adjustmentQty,
                        'old_qty'      => $oldQty,
                        'new_qty'      => $newQty,
                        'remarks'      => $remarks,
                    ]);

                    $processedCount++;
                    continue;
                }

                /** ✴ Check Product Stock */
                $productStock = StockProduct::whereHas('product', function ($q) use ($fabricOrProduct) {
                    $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($fabricOrProduct))]);
                })->first();

                if ($productStock) {
                    $oldQty = floatval($productStock->qty_in_pieces);
                    $newQty = $oldQty - $adjustmentQty;

                    if ($newQty < 0) {
                        $errors[] = "Insufficient product stock '{$fabricOrProduct}'. Current: {$oldQty}, Deducting: {$adjustmentQty}";
                        $errorCount++;
                        continue;
                    }

                    $productStock->update(['qty_in_pieces' => $newQty]);

                    StockAdjustmentLog::create([
                        'fabric_id'  => null,
                        'product_id' => $productStock->product_id,
                        'adjustment'   => -$adjustmentQty,
                        'old_qty'      => $oldQty,
                        'new_qty'      => $newQty,
                        'remarks'      => $remarks,
                    ]);

                    $processedCount++;
                } else {
                    $errors[] = "'{$fabricOrProduct}' not found in fabrics or products.";
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "✔ Stock adjustment completed. Processed: {$processedCount}";
            if ($errorCount > 0) {
                $message .= "Errors: {$errorCount} | " . implode(' | ', array_slice($errors, 0, 3));
            }

            session()->flash($errorCount > 0 ? 'error' : 'message', $message);
            $this->dispatch('close_modal');
            $this->reset(['csvFile']); 

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Adjustment Upload Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $logData = StockAdjustmentLog::with('fabric','product')->latest()->paginate(10);
        return view('livewire.stock.stock-adjustment', compact('logData'));
    }
}
