<?php

namespace App\Http\Livewire\Fabric;

use Livewire\Component;
use App\Models\FabricCategory;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;


class FabricCategoryIndex extends Component
{
    use WithPagination;

    public $fabricCategoryId,$title,$search;
     protected $paginationTheme = 'bootstrap'; 
    
      public function updatingSearch()
    {
        $this->resetPage(); 
    }

    public function store(){
        $this->validate([
            'title' => 'required|unique:fabric_categories,title'
        ]);

        FabricCategory::create([
            'title' => $this->title
        ]);

        session()->flash('success','Fabric Category Created Successfully');
        $this->resetFields();
    }

    public function resetFields(){
        $this->title = '';
        $this->fabricCategoryId = null;
    }

    public function edit($id){
        $fabricCategory = FabricCategory::findOrFail($id);
        $this->fabricCategoryId = $fabricCategory->id;
        $this->title = $fabricCategory->title;
    }

    public function update(){
          $this->validate([
          'title' => [
            'required',
                Rule::unique('fabric_categories', 'title')->ignore($this->fabricCategoryId),
            ],
        ]);
        $fabricCategory = FabricCategory::findOrFail($this->fabricCategoryId);
        $fabricCategory->update([
            'title' => $this->title
        ]);

         session()->flash('message', 'Category updated successfully!');
        $this->resetFields();
    }

    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId'=>$id]);
    }

    public function destroy($id){
        $fabricCategory = FabricCategory::findOrFail($id);
        $fabricCategory->delete();
        session()->flash('message','Fabric Deleted Successfully');
    }

    public function toggleStatus($id){
        $fabricCategory = FabricCategory::findOrFail($id);
        $fabricCategory->status = !$fabricCategory->status;
        $fabricCategory->save();
        session()->flash('message','Fabric Status Updated Successfully');

    }

    public function render()
    {
         $categories = FabricCategory::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('title', 'desc')
            ->paginate(10);
        return view('livewire.fabric.fabric-category-index',compact('categories'));
    }
}
