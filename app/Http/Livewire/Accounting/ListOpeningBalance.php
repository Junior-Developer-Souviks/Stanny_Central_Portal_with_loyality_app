<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Journal;

class ListOpeningBalance extends Component
{
    public function confirmDelete($id){
        $this->dispatch('showDeleteConfirm',['itemId' => $id]);
    }
    public function mount(){

    }

    public function DeleteItem($id){
        $ledger = Ledger::findOrFail($id);
        $payment_id = $ledger->payment_id;
        Ledger::where('id',$id)->delete();  
        Payment::where('id',$payment_id)->delete();
        Journal::where('payment_id',$payment_id)->delete();
        // $this->mount();
        session()->flash('success','Opening balance deleted successfully.');
       
    }
    public function render()
    {
        $data = Ledger::with('customer','payment')->where('purpose','opening_balance')->where('user_type','customer')->orderBy('id','desc')->get();
        return view('livewire.accounting.list-opening-balance',[
            'openingBalances' => $data
        ]);
    }
}
