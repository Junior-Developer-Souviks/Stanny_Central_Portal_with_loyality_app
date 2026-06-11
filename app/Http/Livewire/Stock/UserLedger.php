<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Ledger;
use Carbon\Carbon;

class UserLedger extends Component
{
    public $userType = '';
    public $search = '';
    public $results = [];
    public $selectedUser = '';
    public $from_date = ''; 
    public $to_date = ''; 
    public $ledgerData = [];

    public function mount(){
        $this->setDefaultDates();
    }

    public function ChangeUsertype($value){
        $this->userType = $value;
        $this->results = [];
        $this->ledgerData = [];
        $this->setDefaultDates();
    }

    public function searchUsers(){
        if(!empty($this->search)){
            if($this->userType == 'customer'){
                $this->results = User::where('user_type',1)
                                      ->where('name','like','%'.$this->search.'%')
                                      ->pluck('name')
                                      ->toArray();
            }else{
                $this->results = [];
            }
        }else {
            // Clear the table when search input is empty
            $this->results = [];
            $this->selectedUser = '';
            $this->ledgerData = [];
        }
    }

    public function setDefaultDates()
    {
        $this->from_date = Carbon::now()->startOfMonth()->toDateString(); // First day of the current month
        $this->to_date = Carbon::now()->toDateString(); // Today's date
    }

    public function selectUser($name){
        $user = User::where('name',$name)->first();
        if($user){
            $this->selectedUser = $user->id; 
            $this->search = $name; 
            $this->results = []; 
            $this->ledgerData = $this->getLedgerData();
        }
    }

    public function updateDate($propertyName){
         $this->ledgerData = $this->getLedgerData();
    }

    private function getLedgerData(){
        if (!$this->selectedUser) {
            return []; // If no user is selected, return empty data
        }
        $query = Ledger::where('user_id', $this->selectedUser);
        if($this->from_date) {
            $query->where('transaction_date', '>=', $this->from_date);
        }
        
        if($this->to_date) {
            $query->where('transaction_date', '<=', $this->to_date);
        }
        $data = $query->get(); // Fetch ledger data for the selected customer and date range
        return $data->isEmpty() ? [] : $data;
    }
    
    public function render()
    {
        return view('livewire.stock.user-ledger');
    }
}
