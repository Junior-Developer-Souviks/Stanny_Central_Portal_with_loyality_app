<?php

namespace App\Http\Livewire\Product;


use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Fabric;
use App\Models\FabricCategory;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FabricsImport;
use App\Exports\FabricsExport;
use App\Exports\SampleFabricExport;



class FabricIndex extends Component
{
    use WithFileUploads;
    use WithPagination;
    public  $image, $title,$category,$pseudo_name, $status = 1, $fabricId,$threshold_price;
    public $search = '';
    public $file;
    public $processedFileHash = null; // Store the hash of the last processed file
    protected $paginationTheme = 'bootstrap'; 
    public $fabricCategories = [];
    public $fabric_category;
    public $latestTitle;
    public $latestPseudoName;
    
    public function mount(){
        $this->fabricCategories = FabricCategory::where('status',1)->get();
    }

    public function loadLatestCategoryData(){
        if($this->category){
            $latestFabricDetails = Fabric::where('fabric_category_id',$this->category)
                                    ->orderByRaw("CAST(SUBSTRING_INDEX(title, ' ', -1) AS UNSIGNED) DESC")
                                    ->first();
            if($latestFabricDetails){
                $this->latestTitle = $latestFabricDetails->title;
                $this->latestPseudoName = $latestFabricDetails->pseudo_name;
            }else{
                $this->latestTitle = null;
                $this->latestPseudoName = null;
            }
        }
    }

    public function FabricCategoryFilter(){
         $this->resetPage(); 
    }

    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }
    
    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);
    
        try {
            $import = new FabricsImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $this->file);
             $messages = [];

           // 1. Check duplicate error
            if ($duplicateError = $import->getDuplicateError()) {
                $messages[] = $duplicateError;
            }

             // 2. Check category errors
            $categoryErrors = $import->getCategoryErrors();
            if (!empty($categoryErrors)) {
                $messages = array_merge($messages, $categoryErrors);
            }
    
            if (!empty($messages)) {
                session()->flash('error', implode('<br>', $messages));
            } else {
                session()->flash('success', 'File imported successfully.');
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            session()->flash('error', 'Error importing file: ' . $e->getMessage());
        }
    
        $this->reset('file');
    }
    
    
    // Export Fabrics
    public function export()
    {
        return Excel::download(new FabricsExport(), 'fabrics.csv');
    }

    
    public function store()
    {
        // dd($this->all());
        $this->validate([
             'category' =>[
                 'required',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:fabrics,title', 
            ],
            'pseudo_name' => [
                'required',
                'unique:fabrics,pseudo_name',
                'max:255'
            ],
            'image' => [
                'nullable',
                'mimes:jpg,png,jpeg,gif',
            ],
            'threshold_price' => [
                'required',
                'numeric',
                'min:1',
            ],
        ]);

        $absolutePath = null;
        
        if($this->image){
            $imagePath = $this->image->store("fabrics",'public');
            $absolutePath = "storage/".$imagePath;
        }
        

        Fabric::create([
            'collection_id' => 1,
            'fabric_category_id' => $this->category, 
            'title' => $this->title,
            'pseudo_name' => $this->pseudo_name,
            'threshold_price' => $this->threshold_price,
            'image' =>  $absolutePath,
            'status' => $this->status,
        ]);
        
        $this->title = null;
        $this->image = null;
        $this->threshold_price = null;
        // Refresh the fabrics list for the current product
        session()->flash('message', 'Fabric created successfully!');
        $this->resetPage(); // Refresh the list
    }

    // Edit Fabric
    public function edit($id)
    {
        $fabric = Fabric::findOrFail($id);
        $this->fabricId = $fabric->id;
        $this->title = $fabric->title;
        $this->category = $fabric->fabric_category_id;
        $this->pseudo_name = $fabric->pseudo_name;
        $this->threshold_price = $fabric->threshold_price;
        
        $this->image = $fabric->image;
        $this->status = $fabric->status;
    }
    // Update Fabric
    public function update()
    {
        $this->validate([
            'category' =>[
                 'required',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fabrics', 'title')->ignore($this->fabricId), 
            ],
            'pseudo_name' => [
                'required',
                'max:255',
                Rule::unique('fabrics', 'pseudo_name')->ignore($this->fabricId),
            ],
            // 'image' => [
            //     'nullable',
            //     'mimes:jpg,png,jpeg,gif',
            // ],
             'image' => $this->image instanceof \Livewire\TemporaryUploadedFile 
                ? 'nullable|mimes:jpg,png,jpeg,gif' 
                : 'nullable',
            'threshold_price' => [
                'required',
                'numeric',
                'min:1',
            ],

        ]);
        
        $fabric = Fabric::findOrFail($this->fabricId);
        $imagePath = $fabric->image;
        if ($this->image instanceof \Illuminate\Http\UploadedFile) {
            // Store new image
            $newImagePath = $this->image->store("fabrics", 'public');
            $imagePath = "storage/" . $newImagePath;
        }
        $fabric->update([
            'title' => $this->title,
            'fabric_category_id' => $this->category, 
            'pseudo_name' => $this->pseudo_name,
            'threshold_price' => $this->threshold_price,
            'image' => $imagePath,
            'status' => $this->status,
        ]);
        
        $this->title = null;
        $this->image = null;
        $this->threshold_price = null;
        $this->category = null;
        $this->pseudo_name = null;

        session()->flash('message', 'Fabric updated successfully!');
       $this->resetFields();
    }

    // Delete Fabric
    public function destroy($id)
    {
        Fabric::findOrFail($id)->delete();
        session()->flash('message', 'Fabric deleted successfully!');
        $this->fabrics = Fabric::orderBy('id', 'desc')->get();
    }

    // Toggle Status
    public function toggleStatus($id)
    {
        $fabric = Fabric::findOrFail($id);
        $fabric->update(['status' => !$fabric->status]);
        session()->flash('message', 'Fabric status updated successfully!');
    }

    public function downloadFabricCSV()
    {
        $filePath = public_path('assets/csv/sample_fabrics.csv'); // Correct file path

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function resetFields(){
        $this->reset(['fabricId','title','image','threshold_price','category','pseudo_name']);
    }

    public function resetForm(){
        $this->reset(['fabric_category']);
    }

    // Render Method with Search and Pagination
    public function render()
    {
        $query = Fabric::where('title', 'like', "%{$this->search}%");
        if ($this->fabric_category) {
           $query->where('fabric_category_id', $this->fabric_category);
        }

        $fabrics =  $query->orderBy('fabric_category_id', 'asc')
                          ->orderByRaw("CAST(SUBSTRING_INDEX(title, ' ', -1) AS UNSIGNED) ASC")
                          ->paginate(10);

        return view('livewire.product.fabric-index', [
            'fabrics' => $fabrics,
        ]);
    }
}