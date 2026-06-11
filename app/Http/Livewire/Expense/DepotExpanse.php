<?php

namespace App\Http\Livewire\Expense;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Collection;
use App\Models\User;
use Carbon\Carbon;

class DepotExpanse extends Component
{
    use WithPagination;

    // Daily Expenses Properties
    public $start_date;
    public $end_date;
    public $search = '';

    // Daily Collection Properties
    public $start_date_collection;
    public $end_date_collection;
    public $search_collection = '';

    public $activeTab = 'dailyCollection';

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');

        $this->start_date_collection = now()->format('Y-m-d');
        $this->end_date_collection = now()->format('Y-m-d');
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    // public function render()
    // {
    //     // **Query for Daily Expenses**
    //     $query = Payment::with(['user', 'supplier'])->where('payment_for','Debit')->latest('updated_at');

    //     // Apply date filter for expenses
    //     if ($this->start_date && $this->end_date) {
    //         $query->whereBetween('updated_at', [
    //             $this->start_date . ' 00:00:00',
    //             $this->end_date . ' 23:59:59'
    //         ]);
    //     }

    //     // Search filter for expenses
    //     if (!empty($this->search)) {
    //         $query->where(function ($q) {
    //             $q->whereHas('staff', function ($q) {
    //                 $q->where('name', 'like', '%' . $this->search . '%');
    //             })->orWhereHas('supplier', function ($q) {
    //                 $q->where('name', 'like', '%' . $this->search . '%');
    //             });
    //         });
    //     }

    //     $totalAmount = $query->sum('amount');
    //     $payments = $query->paginate(10);

    //     // **Query for Daily Collections**
    //     $collectionQuery = Payment::with(['staff', 'supplier'])->latest('updated_at');

    //     // Apply date filter for collections
    //     if ($this->start_date_collection && $this->end_date_collection) {
    //         $collectionQuery->whereBetween('updated_at', [
    //             $this->start_date_collection . ' 00:00:00',
    //             $this->end_date_collection . ' 23:59:59'
    //         ]);
    //     }

    //     // Search filter for collections
    //     if (!empty($this->search_collection)) {
    //         $collectionQuery->where(function ($q) {
    //             $q->whereHas('staff', function ($q) {
    //                 $q->where('name', 'like', '%' . $this->search . '%');
    //             })->orWhereHas('supplier', function ($q) {
    //                 $q->where('name', 'like', '%' . $this->search . '%');
    //             });
    //         });
    //     }

    //     $totalCollection = $collectionQuery->sum('amount');
    //     $collections = $collectionQuery->paginate(10);

    //     return view('livewire.expense.depot-expanse', compact('payments', 'totalAmount', 'collections', 'totalCollection'));
    // }

    public function render()
    {
        // **Query for Daily Expenses**
        $query = Payment::with(['user', 'supplier'])
            ->where('payment_for', 'Debit')
            ->latest('updated_at');

        // Clone query before modifying
        $expenseQuery = clone $query;

        // Apply date filter for expenses
        if ($this->start_date && $this->end_date) {
            $expenseQuery->whereBetween('updated_at', [
                $this->start_date . ' 00:00:00',
                $this->end_date . ' 23:59:59'
            ]);
        }

        // Search filter for expenses
        if (!empty($this->search)) {
            $expenseQuery->where(function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('supplier', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $totalAmount = $expenseQuery->sum('amount');
        $payments = $expenseQuery->paginate(10);

        // **Query for Daily Collections**
        $collectionQuery = Payment::with(['user', 'supplier'])
            ->where('payment_for', 'Credit')
            ->latest('updated_at');

        // Clone query before modifying
        $collectionsQuery = clone $collectionQuery;

        // Apply date filter for collections
        if ($this->start_date_collection && $this->end_date_collection) {
            $collectionsQuery->whereBetween('updated_at', [
                $this->start_date_collection . ' 00:00:00',
                $this->end_date_collection . ' 23:59:59'
            ]);
        }

        // Search filter for collections
        if (!empty($this->search_collection)) {
            $collectionsQuery->where(function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search_collection . '%');
                })->orWhereHas('supplier', function ($q) {
                    $q->where('name', 'like', '%' . $this->search_collection . '%');
                });
            });
        }

        $totalCollection = $collectionsQuery->sum('amount');
        $collections = $collectionsQuery->paginate(10);

        return view('livewire.expense.depot-expanse', compact('payments', 'totalAmount', 'collections', 'totalCollection'));
    }

}
