<?php

namespace App\Http\Livewire\Product;

use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Collection;
use Livewire\Component;
use App\Helpers\Helper;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class MasterCategory extends Component
{
    use WithFileUploads, WithPagination;

    public $collection_id;
    public $title, $status = 1, $categoryId, $image, $search = '',$short_code;

    public function updatingSearch()
    {
        $this->resetPage(); 
    }
    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }

    public function store()
    {
        // dd($this->all());
        $this->validate([
            'short_code' => 'required|max:255',
            'collection_id' => 'required',
            'short_code' => 'required|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'title' => 'required|string|max:255'
        ],
        [
            'short_code.required' => 'The short code field is required.',
            'short_code.max' => 'The short code must not exceed 255 characters.',
            'collection_id.required' => 'The collection field is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, svg, gif.',
            'image.max' => 'The image size must not exceed 2MB.',
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'title.unique' => 'The title has already been taken.',
        ]);
                 $absoluteAssetPath = null;  // Initialize the variable in case no image is uploaded
            if ($this->image && $this->image instanceof \Illuminate\Http\UploadedFile) {
                $absoluteAssetPath = 'storage/' . $this->image->store('category_image', 'public');
            }

        Category::create([
            'collection_id' => $this->collection_id,
            'title' => $this->title,
            'short_code' => $this->short_code,
            'status' => $this->status,
            'image' => $absoluteAssetPath,
        ]);

        session()->flash('message', 'Category created successfully!');
        $this->resetFields();
       
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->collection_id = $category->collection_id;
        $this->categoryId = $category->id;
        $this->title = $category->title;
        $this->short_code = $category->short_code;
        $this->status = $category->status;
        $this->image = $category->image;
    }

    public function update()
    {
       
        $this->validate([
            'collection_id' => 'required',  
            'title' => 'required|string|max:255',
            'short_code' => 'required|max:255',
        ],
        [
            'collection_id.required' => 'The collection field is required.',
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'title.unique' => 'The title has already been taken.',
            'short_code.required' => 'The short code field is required.',
            'short_code.max' => 'The short code must not exceed 255 characters.',
        ]
    );
        DB::beginTransaction(); // Start the transaction
        try {
            // Find the category
            $category = Category::findOrFail($this->categoryId);
            
            $absoluteAssetPath = $category->image; // Default to the current image if no new one is uploaded
            if ($this->image && $this->image instanceof \Illuminate\Http\UploadedFile) {
                $absoluteAssetPath = 'storage/' . $this->image->store('category_image', 'public');
            }
    
            // Update the category
            $category->update([
                'collection_id' => $this->collection_id,
                'title' => $this->title,
                'short_code' => $this->short_code,
                'status' => $this->status,
                'image' => $absoluteAssetPath,
            ]);

            DB::commit(); // Commit the transaction
            session()->flash('message', 'Category updated successfully!');
            $this->resetFields();

        } catch (\Throwable $e) {
            DB::rollBack(); // Rollback the transaction in case of error

            // Flash an error message to the session or return a response
            session()->flash('error', $e->getMessage());
            // dd($e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        session()->flash('message', 'Category deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->status = !$category->status;
        $category->save();

        session()->flash('message', 'Category status updated successfully!');
    }
    public function FindCategory($keywords){
        $this->search = $keywords;
    }
    public function resetFields()
    {
        $this->title = '';
        $this->status = 1;
        $this->categoryId = null;
        $this->image = null;
        $this->collection_id = null;
        $this->short_code = null;
    }
    public function resetSearch()
    {
        $this->search = '';
        $this->title = '';
    }
    public function render()
    {
        $collections = Collection::pluck('title', 'id');
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('title', 'desc')
            ->paginate(5);

        return view('livewire.product.master-category', compact('categories','collections'));
    }

}
