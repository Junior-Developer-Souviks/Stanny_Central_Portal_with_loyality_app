<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use App\Helpers\Helper;
use App\Exports\UserAddressExport;
use App\Imports\UserAddressImport;
use App\Exports\UsersAndAddressesExport;
use App\Imports\UsersWithAddressesImport;
use App\Exports\SampleUserAndAddressExport;
use Illuminate\Support\Facades\Auth;

// use Livewire\WithFileUploads; // Import file upload trait



class CustomerIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search;
    public $file;

    protected $updatesQueryString = ['search'];
    protected $paginationTheme = 'bootstrap'; 

    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }
    public function updatingSearch()
    {
        $this->resetPage(); 
    }

    public function deleteCustomer($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            session()->flash('success', 'Customer deleted successfully');
        } else {
            session()->flash('error', 'Customer not found');
        }
    }

    public function toggleStatus($id){
        $user = User::find($id);
        $user->status = !$user->status;
        $user->save();
        session()->flash('success','Customer status updated successfully');
    }
    // public function FindCustomer($keywords){
    //     $this->search = $keywords;
    // }
    public function resetForm(){
        $this->reset(['search']);
    }
    
    protected $rules = [
        'file' => 'required|mimes:xlsx,csv|max:2048', // Validate file type and size
    ];

   

    public function import()
    {
        $this->validate(); // Validate the file input

        // Store the uploaded file
        $path = $this->file->store('imports');
        // try {
            // Perform the import
            Excel::import(new UsersWithAddressesImport, storage_path('app/' . $path));

            // Reset file input
            $this->reset('file');

            // Check if any validation errors occurred during import
            if (session()->has('import_errors')) {
                return;
            }

            // Send success message
            $this->dispatch('close-import-modal');
            session()->flash('success', 'Users imported successfully!');
        // } catch (\Exception $e) {
        //     session()->flash('error', 'Import failed: ' . $e->getMessage());
        // }
    }

    
    // Export Function
    public function export()
    {
        // return Excel::download(new UsersExport, 'users.xlsx');
        return Excel::download(new UsersAndAddressesExport(), 'customers_and_addresses.csv');
    }

    public function sampleExport()
    {
        // return Excel::download(new UsersExport, 'users.xlsx');
        return Excel::download(new SampleUserAndAddressExport(), 'customers_and_addresses.csv');
    }
    public function exportAddresses()
    {
        return Excel::download(new UserAddressExport, 'user_addresses.xlsx');
    }


    public function downloadCustomerCSV()
    {
        $filePath = public_path('assets/csv/customers_details.csv'); // Correct file path

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            session()->flash('error', 'File not found.');
        }
    }

    public function render()
    {
        $auth = Auth::guard('admin')->user();

        $users = User::where('user_type', 1)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('prefix', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('whatsapp_no', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                       ->orWhereRaw("CONCAT(prefix, ' ', name) LIKE ?", ['%' . $this->search . '%'])
                       ->orWhereRaw("CONCAT(country_code_phone, ' ', phone) LIKE ?", ['%' . $this->search . '%'])
                       ->orWhereRaw("CONCAT(country_code_phone, '', phone) LIKE ?", ['%' . $this->search . '%']);
            })
            ->when(!$auth->is_super_admin, fn($query) => $query->where('created_by', $auth->id)) // Restrict non-admins
            ->orderBy('created_at', 'desc') // Sort by latest
            ->paginate(10);
        
        return view('livewire.customer-index', compact('users'));
    }
}
