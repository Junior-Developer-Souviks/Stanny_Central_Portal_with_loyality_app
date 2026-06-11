<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Fabric;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $category = Category::firstOrCreate(['title' => $row['category_name']]);
        $collection = Collection::firstOrCreate(['title' => $row['collection_name']]);

        // Handle image import (URL or base64)
        // $productImage = $this->saveImage($row['product_image']);

        $product = Product::updateOrCreate(
            ['product_code' => $row['product_code']], // Unique identifier
            [
                'category_id' => $category->id,
                'collection_id' => $collection->id,
                'name' => $row['product_name'],
                'short_description' => $row['short_description'],
                'description' => $row['description'],
                'gst_details' => $row['gst_details'],
                // 'product_image' => $productImage,
                'profile_image' => $row['product_images'] ?? null,
            ]
        );

        // Attach fabrics
        $fabricNames = array_map('trim', explode(',', $row['fabrics'])); // Trim spaces from names
        $fabricIds = Fabric::whereIn('title', $fabricNames)->pluck('id')->toArray();
        $product->fabrics()->sync($fabricIds);
        

        // Attach suppliers
        // $supplierNames = explode(',', $row['supplier_names']);
        // $supplierIds = Supplier::whereIn('name', array_map('trim', $supplierNames))->pluck('id')->toArray();
        // $product->suppliers()->sync($supplierIds);

        return $product;
    }

    private function saveImage($imageSource)
    {
        if (filter_var($imageSource, FILTER_VALIDATE_URL)) {
            $imageContents = file_get_contents($imageSource);
            $imageName = 'product_images/' . Str::random(10) . '.jpg';
            Storage::put($imageName, $imageContents);
            return $imageName;
        } elseif (preg_match('/^data:image\/(\w+);base64,/', $imageSource)) {
            $data = substr($imageSource, strpos($imageSource, ',') + 1);
            $data = base64_decode($data);
            $imageName = 'product_images/' . Str::random(10) . '.jpg';
            Storage::put($imageName, $data);
            return $imageName;
        }

        return null;
    }
}
