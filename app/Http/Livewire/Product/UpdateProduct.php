<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use Livewire\WithFileUploads;
use App\Models\Collection;
use App\Models\CollectionType;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;
use App\Models\Fabric;
use Illuminate\Validation\Rule;


class UpdateProduct extends Component
{

    use WithFileUploads;

    public $product_id;
    public $collection;
    public $category_id;
    // public $sub_category_id;
    public $name;
    public $product_code;
    public $short_description;
    public $description;
    public $gst_details;
    public $product_image;
    public $categories = []; // For categories dropdown
    public $subCategories = []; // For subcategories dropdown
    PUBLIC $existing_image;
    public $Collections = [];
     public $selectedFabrics = [];
    public $fabrics = [];
    public $existingImages = [];
    public $multipleImages = [];
    public $showAdditionalImageField = false; // To control visibility of the additional image field
    public $additionalImage; // For storing the uploaded additional image
    public $existingAdditionalImage; // For displaying the current additional image (if any)
    public $selectAll = false;

// Modified mount function
public function mount($product_id)
{
    $product = Product::with('images')->findOrFail($product_id);

    $this->product_id = $product->id;
    $this->collection = $product->collection_id;
    $this->category_id = $product->category_id;
    $this->name = $product->name;
    $this->product_code = $product->product_code;
    $this->short_description = $product->short_description;
    $this->description = $product->description;
    $this->gst_details = $product->gst_details;

    // Load categories based on the product's collection
    $this->categories = Category::where('collection_id', $this->collection)
        ->where('status', 1)
        ->orderBy('title', 'ASC')
        ->get();

    // Store existing images and additional image
    $this->existing_image = $product->product_image; // Store existing main image path
    $this->existingImages = $product->images->pluck('image', 'id')->toArray(); 
    $this->existingAdditionalImage = $product->additional_image ?? null; // Store existing additional image path

    // Check if the collection is "garment" to show additional image field
    $collection = Collection::find($this->collection);
    $this->showAdditionalImageField = $collection && strtolower($collection->title) === 'garment';

    $this->Collections = Collection::orderBy('title', 'ASC')->get();
    $this->fabrics = Fabric::all();
    $this->selectedFabrics = $product->fabrics->pluck('id')->toArray();
      // Check if all fabrics are selected
    $this->selectAll = count($this->selectedFabrics) === $this->fabrics->count();
}



    public function toggleSelectAll(){
        if($this->selectAll){
            $this->selectedFabrics = $this->fabrics->pluck('id')->toArray();
        }else{
            $this->selectedFabrics = [];
        }
    }
  
    public function GetCollection($id){
        $selectedCollection = Collection::find($id);
        if ($selectedCollection && strtolower($selectedCollection->title) === 'garment') {
            $this->showAdditionalImageField = true;
        } else {
            $this->showAdditionalImageField = false;
        }

        $this->categories = Category::where('collection_id', $id)
            ->where('status', 1)
            ->orderBy('title', 'ASC')
            ->get() ?? collect();

    }

    
  

  
    public function update()
    {
        try {
            // Validate input data
            $this->validate([
                'collection' => 'required',
                'category_id' => 'required',
                'selectedFabrics' => 'nullable|array',
                'name' => 'required|string|max:255',
                'product_code' => 'required|string|max:50',
                'short_description' => 'nullable|string|max:500',
                'description' => 'nullable|string',
                'gst_details' => 'nullable|numeric|min:0',
                'product_image' => 'nullable|image|max:1024', // 1MB max size
            ]);
    
            \DB::beginTransaction(); // Start transaction
    
            $product = Product::findOrFail($this->product_id);
            
            // Handle product image
            $imagePath = $product->product_image; // Use existing image by default
            if ($this->product_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $imagePath = $this->product_image->store('uploads/product', 'public');
                
                // Delete the old image if it exists
                if ($product->product_image && \Storage::disk('public')->exists($product->product_image)) {
                    \Storage::disk('public')->delete($product->product_image);
                }
            }
    
            // Handle multiple image uploads
            if ($this->multipleImages) {
                foreach ($this->multipleImages as $image) {
                    $path = $image->store('uploads/product_images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                    ]);
                }
            }
    
            // Update product details
            $product->update([
                'collection_id' => $this->collection,
                'category_id' => $this->category_id,
                'name' => $this->name,
                'product_code' => $this->product_code,
                'short_description' => $this->short_description,
                'description' => $this->description,
                'gst_details' => $this->gst_details,
                'product_image' => $imagePath, // Use new or existing image
            ]);
    
            // Sync fabrics
            $product->fabrics()->sync($this->selectedFabrics);
    
            \DB::commit(); // Commit transaction
    
            session()->flash('message', 'Product updated successfully!');
            return redirect()->route('product.view');
        } catch (\Exception $e) {
            \DB::rollBack(); // Rollback transaction on error
            dd($e->getMessage());
            // Show an error message to the user
            session()->flash('error', 'An error occurred while updating the product. Please try again.');
            return redirect()->back()->withInput(); // Redirect back with input
        }
    }
    
    public function removeExistingImage($imageId)
    {
        $image = ProductImage::find($imageId);

        if ($image) {
            // Delete the image file from storage
            \Storage::disk('public')->delete($image->image);

            // Delete the record from the database
            $image->delete();

            // Remove from the array of existing images
            unset($this->existingImages[$imageId]);
        }
    }
    public function removeNewImage($index)
    {
        unset($this->multipleImages[$index]);
        $this->multipleImages = array_values($this->multipleImages); // Reindex array
    }
    public function render()
    {
        return view('livewire.product.update-product');
    }
}
