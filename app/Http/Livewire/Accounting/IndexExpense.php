<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Designation;


class IndexExpense extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $search;
    public $paymentDate = '';
    public $canApprove = false;
   
    public function mount()
    {
        $auth = auth()->guard('admin')->user();

        $this->canApprove =  $auth->is_super_admin || Designation::where('id', $auth->designation)
                ->whereHas('permissions', function ($query) {
                    $query->where('route', 'admin.accounting.expense.details');
                })
                ->exists();
    }
    
   public function searchExpense($value)
    {
       
        $this->search = $value;
    }
    
    public function AddPaymentDate($value){
        $this->paymentDate = $value;
    }
    
    public function export()
    {
        $auth = auth()->guard('admin')->user();
    
        $isAuthorizedViewer = $auth->is_super_admin || ($auth->designation == 14);
    
        $expenses = Payment::where('payment_for', 'debit')
            ->when(!$isAuthorizedViewer, function ($query) use ($auth) {
                return $query->where('stuff_id', $auth->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('voucher_no', 'like', '%' . $this->search . '%')
                      ->orWhere('amount', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->paymentDate, function ($query) {
                return $query->whereDate('payment_date', $this->paymentDate);
            })
            ->orderBy('payment_date', 'desc')
            ->get();
    
        $fileName = 'expenses_' . now()->format('Y_m_d_H_i_s') . '.csv';
    
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];
    
        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
    
            // CSV Header
            fputcsv($file, [
                'Date',
                'Transaction ID',
                'Amount',
                'Staff Name',
                'Created From',
                'Approval Status',
            ]);
    
            foreach ($expenses as $item) {
                fputcsv($file, [
                    $item->payment_date ? date('d/m/Y', strtotime($item->payment_date)) : '',
                    $item->voucher_no,
                    $item->amount,
                    $item->staff ? $item->staff->name : "",
                    $item->created_from,
                    $item->is_ledger_added ? 'Approved' : 'Not Approved',
                ]);
            }
    
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }

   
    
  
  
    public function render()
    {
        $auth = auth()->guard('admin')->user();

        $isAuthorizedViewer = $auth->is_super_admin || ($auth->designation == 14);
             
        $expenses = Payment::where('payment_for', 'debit')
            //  AUTH FILTER (MOST IMPORTANT PART)
            ->when(!$isAuthorizedViewer, function ($query) use ($auth) {
                return $query->where('stuff_id', $auth->id); 
                // OR change to user_id/staff_id based on your DB structure
            })
    
            // SEARCH
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('voucher_no', 'like', '%' . $this->search . '%')
                      ->orWhere('amount', 'like', '%' . $this->search . '%');
                });
            })
    
            // DATE FILTER
            ->when($this->paymentDate, function ($query) {
                return $query->whereDate('payment_date', $this->paymentDate);
            })
    
            ->orderBy('payment_date', 'desc')
            ->paginate(10);
        return view('livewire.accounting.index-expense', compact('expenses'));
    }
}
