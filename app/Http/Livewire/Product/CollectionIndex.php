<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Collection;
use App\Models\CollectionType;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CollectionIndex extends Component
{
    use WithPagination;

    public $title;
    public $status = 1;
    public $search = '';
    public $collectionId;
    
    public function mount()
    {
        
    }

    public function store()
    {
        $this->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:collections,title',
            ],
        ]);

        Collection::create([
            'title' => ucfirst($this->title),
        ]);

        $this->resetForm();

        session()->flash('message', 'Collection created successfully!');
    }

    public function edit($id)
    {
        $collection = Collection::findOrFail($id);
        $this->collectionId = $collection->id;
        $this->title = $collection->title;
    }

    public function update()
    {
        $this->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('collections', 'title')->ignore($this->collectionId),
            ],
        ]);

        $collection = Collection::findOrFail($this->collectionId);
        $collection->update([
            'title' => ucfirst($this->title),
        ]);

        $this->resetForm();

        session()->flash('message', 'Collection updated successfully!');
    }

    public function toggleStatus($id)
    {
        $collection = Collection::findOrFail($id);
        $collection->update(['status' => !$collection->status]);
        session()->flash('message', 'Collection status updated successfully!');
    }

    public function resetForm()
    {
        $this->title = null;
        $this->short_code = null;
        $this->collection_type = null;
        $this->collectionId = null;
    }

    public function render()
    {
        $collections = Collection::where('title', 'like', "%{$this->search}%")
            ->orderBy('title', 'ASC')
            ->paginate(10);

        return view('livewire.product.collection-index', [
            'collections' => $collections,
        ]);
    }
}
