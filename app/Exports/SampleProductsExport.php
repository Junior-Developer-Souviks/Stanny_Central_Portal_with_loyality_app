<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SampleProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::with(['category', 'collection', 'fabrics'])
                      ->whereNull('deleted_at')
                      ->limit(2)
                      ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Collection Name', 'Category Name', 'Product Name', 
            'Product Code', 'Short Description', 'Description', 'GST Details', 'Fabrics','Status'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            optional($product->collection)->title,
            optional($product->category)->title,
            $product->name,
            $product->product_code,
            $product->short_description,
            $product->description,
            $product->gst_details,
            // asset('storage/' . $product->product_image),
            $product->fabrics->pluck('title')->implode(', '),
            $product->status ? 'Active' : 'Inactive',
            // $product->suppliers->pluck('name')->implode(', '),
        ];
    }
}
