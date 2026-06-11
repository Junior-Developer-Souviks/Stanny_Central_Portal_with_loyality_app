<?php

namespace App\Exports;

use App\Models\Fabric;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SampleFabricExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Fabric::with('collection')
        ->select('id', 'collection_id', 'title', 'threshold_price', 'status')
        ->limit(2)
        ->get();
    }

    // Define Column Headers
    public function headings(): array
    {
        return ['ID', 'Collection Title', 'Title', 'Threshold Price', 'Status'];
    }

    // Map the data to the correct format
    public function map($fabric): array
    {
        return [
            $fabric->id,
            optional($fabric->collection)->title ?? 'No Collection', // Get collection title
            $fabric->title,
            $fabric->threshold_price,
            // $fabric->image,
            $fabric->status ? 'Active' : 'Inactive', // Convert status to text
        ];
    }
}
