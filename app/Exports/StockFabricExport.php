<?php

namespace App\Exports;

use App\Models\StockFabric;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class StockFabricExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $startDate;
    protected $endDate;

    public function __construct($search, $startDate, $endDate)
    {
        $this->search = $search;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Fetch data with filters.
     */
    public function collection()
    {
        return StockFabric::with('fabric')
            ->when($this->search, function ($query) {
                $query->whereHas('fabric', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->get();
    }

    /**
     * Set the headings for the exported file.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Fabric Name',
            'Order Quantity (Meters)',
            'GRN Quantity (Meters)',
            'Piece Price',
            'Total Price',
            'Entry Date'
        ];
    }

    /**
     * Map data before exporting.
     */
    public function map($stock): array
    {
        return [
            $stock->id,
            $stock->fabric->title ?? 'N/A',
            $stock->qty_in_meter,
            intval($stock->qty_while_grn),
            number_format($stock->piece_price, 2),
            number_format($stock->total_price, 2),
            // !empty($stock->created_at) ? $stock->created_at->format('d-m-Y') : 'N/A', // Proper Date Format
            $stock->created_at ? Carbon::parse($stock->created_at)->format('Y-m-d H:i:s') : 'N/A',
        ];
    }
}
