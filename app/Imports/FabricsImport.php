<?php

namespace App\Imports;

use App\Models\Fabric;
use App\Models\Collection;
use App\Models\FabricCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FabricsImport implements ToModel, WithHeadingRow
{
    private $duplicates = []; // Store duplicate fabric titles
    private $totalRows = 0; // Store total rows processed
    public $errors = [];
    public function model(array $row)
    {
        // dd($row);
        $this->totalRows++; // Count total rows

        // Get or create the collection ID
        // $collection = Collection::firstOrCreate(['title' => $row['collection_title']]);

        // Find fabric category by title
        $fabricCategory = FabricCategory::where('title', $row['style'])->first();
        if (!$fabricCategory) {
            // store error for missing category
            $this->errors[] = "Category '{$row['style']}' does not exist. Please add it first.";
            return null; // skip this row
        }

        // Check if fabric exists with the same title in the same collection
        $existingFabric = Fabric::where('title', $row['radheys_ref_no'])
            // ->where('collection_id', $collection->id)
             ->where('fabric_category_id', $fabricCategory->id)
            ->first();

        if ($existingFabric) {
            $this->duplicates[] = $row['radheys_ref_no']; // Store duplicate fabric title
            return null; // Skip duplicate entry
        }

        return new Fabric([
            'collection_id' => 1,
            'fabric_category_id' => $fabricCategory->id, 
            'title' => $row['radheys_ref_no'],
            'pseudo_name' => $row['ref_number_company'] ?? null,
            'threshold_price' => $row['threshold_price'] ?? 0,
            // 'image' => $row['image'] ?? null,
            // 'status' => strtolower($row['status']) === 'active' ? 1 : 0,
        ]);
    }

    public function getDuplicateError()
    {
        // If all rows are duplicates, return error message
        if ($this->totalRows > 0 && count($this->duplicates) === $this->totalRows) {
            return 'All data in the file already exists.';
        }
        return null;
    }

    public function getCategoryErrors()
    {
        return $this->errors; 
    }
}
