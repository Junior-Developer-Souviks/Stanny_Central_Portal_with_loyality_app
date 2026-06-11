<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Catalogue;
use App\Models\Page;
use App\Models\CataloguePageItem;
use Livewire\WithPagination;


class CataloguePages extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $pages;
    public $isEditing = false;
    public $editingPageId; 
    public $cataloguePageId;
    public $catalogue_id;
    public $catalogue_name;
    public $page_number;
    public $catalog_items = [''];

    public function mount($catalogue_id)
    {
        $catalogue = Catalogue::with('catalogueTitle')->findOrFail($catalogue_id);
        $this->catalogue_id = $catalogue->id;
        $this->catalogue_name = $catalogue->catalogueTitle->title ?? 'Unknown';
    }


    public function setCatalogueAndPage($catalogue_id, $page_number){
        $catalogue = Catalogue::find($catalogue_id);
        // $this->catalogue_id = $catalogue_id;
        if($catalogue){
            $catalogue_name  = $catalogue->catalogueTitle->title ?? 'Unknown';
        }
        $this->page_number = $page_number;
        $this->cataloguePageId = null; // Reset to null for create mode
        $this->catalog_items = ['']; // Reset items
    }

    public function editCatalogueAndPage($catalogue_id, $page_number){
        $this->catalogue_id = $catalogue_id;
        $this->page_number = $page_number;
        $this->isEditing = true;

        // Fetch catalogue details
        $catalogue = Catalogue::find($catalogue_id);
        if ($catalogue) {
            $this->catalogue_name = $catalogue->catalogueTitle->title ?? 'Unknown';
        }
    
        // Fetch the selected page items
        $page = Page::with('cataloguePageItems')->where('catalogue_id', $catalogue_id)
            ->where('page_number', $page_number)
            ->first();
    
        if ($page) {
            $this->cataloguePageId = $page->id;
            // Populate catalog_items with existing values
            $this->catalog_items = $page->cataloguePageItems->pluck('catalog_item')->toArray();
        } else {
            $this->cataloguePageId = null;
            $this->catalog_items = [''];
        }

        if(empty($this->catalog_items)){
            $this->catalog_items = [''];
        }
      

    }
    

    public function addItem(){
        $this->catalog_items[] = '';
    }

    public function removeItem($index){
        if(count($this->catalog_items) > 1){
            unset($this->catalog_items[$index]);
            $this->catalog_items = array_values($this->catalog_items);
        }
    }



    public function storeOrUpdate()
    {
        $this->validate([
            'page_number'    => 'required',
            'catalog_items.*'  => 'required'
        ], [
            'page_number.required'    => 'Page number is required.',
            'catalog_items.*.required' => 'Page Item is required.',
        ]);

        // Find or create the page
        $page = Page::firstOrCreate([
            'catalogue_id' => $this->catalogue_id,
            'page_number'  => $this->page_number
        ]);

        // Get existing catalog items for this page
        $existingItems = CataloguePageItem::where([
            'catalogue_id' => $this->catalogue_id,
            'page_id'      => $page->id
        ])->pluck('catalog_item')->toArray();

        $incomingItems = array_map('trim', $this->catalog_items);
        
       
        foreach ($incomingItems as $item) {
            if (!empty($item)) {
                CataloguePageItem::updateOrCreate(
                    [
                        'catalogue_id' => $this->catalogue_id,
                        'page_id'      => $page->id,
                        'catalog_item' => $item
                    ],
                    [] // No need to update fields if found
                );
            }
        }

        
        $itemsToDelete = array_diff($existingItems, $incomingItems);

        
        if (!empty($itemsToDelete)) {
            CataloguePageItem::where([
                'catalogue_id' => $this->catalogue_id,
                'page_id'      => $page->id
            ])->whereIn('catalog_item', $itemsToDelete)->delete();
        }

       
        $this->pages = Page::with(['catalogue', 'cataloguePageItems'])
            ->where('catalogue_id', $this->catalogue_id)
            ->get();

        session()->flash('message','Catalogue Page Items updated successfully!');
        $this->resetForm();
    }


    public function resetForm(){
        $this->reset([
          'catalog_items',
          'page_number',
          'isEditing'
        ]);
    }


    public function render()
    {
        $catpages = Page::with(['catalogue', 'cataloguePageItems'])
            ->where('catalogue_id', $this->catalogue_id)
            ->paginate(10);
        return view('livewire.product.catalogue-pages', compact('catpages'));
    }
}
