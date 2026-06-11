<?php

namespace App\Exports;

use App\Models\StockProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockProductExport implements FromCollection, WithHeadings, WithMapping
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
        return StockProduct::with('product')
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
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
            'Product Name',
            'Order Quantity',
            'GRN Quantity',
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
            $stock->product->name ?? 'N/A',
            $stock->qty_in_pieces,
            intval($stock->qty_while_grn),
            number_format($stock->piece_price, 2),
            number_format($stock->total_price, 2),
            optional($stock->created_at)->format('d-m-Y') ?? 'N/A', // Fix Entry Date Format
        ];
    }
}

