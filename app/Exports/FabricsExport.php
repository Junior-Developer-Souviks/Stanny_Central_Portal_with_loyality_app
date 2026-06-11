<?php

namespace App\Exports;

use App\Models\Fabric;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FabricsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Fabric::with('collection','fabric_category')
               ->select( 'collection_id','fabric_category_id', 'title','pseudo_name','threshold_price', 'status')
               ->get();
    }

    // Define Column Headers
    public function headings(): array
    {
        return [ 'Collection Title','Style',"Radhey's Ref. No.",'Ref Number Company','Threshold Price', 'Status'];
    }

    // Map the data to the correct format
    public function map($fabric): array
    {
        return [
            // $fabric->id,
            optional($fabric->collection)->title ?? 'No Collection', // Get collection title
            optional($fabric->fabric_category)->title ?? 'No Category',
            $fabric->title,
            $fabric->pseudo_name,
            $fabric->threshold_price,
            // $fabric->image,
            $fabric->status ? 'Active' : 'Inactive', // Convert status to text
        ];
    }
}
