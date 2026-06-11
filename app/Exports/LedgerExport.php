<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;

class LedgerExport implements FromCollection, WithHeadings, WithMapping
{
    public $collection;
    public $day_opening_amount;
    public $is_opening_bal_showable;
    public $from_date;
    public $to_date;

    public function __construct($collection,$day_opening_amount,$is_opening_bal_showable,$from_date,$to_date)
    {
        $this->collection = $collection;
        $this->day_opening_amount = $day_opening_amount;
        $this->is_opening_bal_showable = $is_opening_bal_showable;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function collection()
    {
        $net_value = $cred_value = $deb_value = 0;
        $cred_ob_amount = $deb_ob_amount = "";
        $data = [];
    
        // Calculate opening balance
        $getCrDrOB = Helper::getCrDr($this->day_opening_amount);
        if ($getCrDrOB == 'Cr') {
            $cred_ob_amount = $this->day_opening_amount;
            $cred_value += $cred_ob_amount;
        } elseif ($getCrDrOB == 'Dr') {
            $deb_ob_amount = Helper::replaceMinusSign($this->day_opening_amount);
            $deb_value += $deb_ob_amount;
        }
    
        if (!empty($this->is_opening_bal_showable)) {
            $net_value += $this->day_opening_amount;
        }
    
        // Add Opening Balance Row if required
        if (!empty($this->collection) && $this->is_opening_bal_showable == 1) {
            $data[] = [
                'Date' => date('d-m-Y', strtotime($this->from_date)),
                'transaction_id' => "",
                'purpose' => "Opening Balance",
                'debit' => Helper::replaceMinusSign($deb_ob_amount),
                'credit' => $cred_ob_amount,
                'closing' => Helper::replaceMinusSign(number_format($this->day_opening_amount)) .' '. Helper::getCrDr($this->day_opening_amount),
            ];
        }
    
        // Process transactions
        foreach ($this->collection as $item) {
            $debit_amount = $credit_amount = '';
    
            if (!empty($item->is_credit)) {
                $credit_amount = number_format((float) $item->transaction_amount);
                $net_value += $item->transaction_amount;
                $cred_value += $item->transaction_amount;
            }
    
            if (!empty($item->is_debit)) {
                $debit_amount = number_format((float) $item->transaction_amount);
                $net_value -= $item->transaction_amount;
                $deb_value += $item->transaction_amount;
            }
    
            $data[] = [
                'Date' => date('d-m-Y', strtotime($item->created_at)),
                'transaction_id' => $item->transaction_id,
                'purpose' => ucwords(str_replace('_', ' ', $item->purpose)) . '(' . ucwords($item->bank_cash).')',
                'debit' => $debit_amount,
                'credit' => $credit_amount,
                'closing' => Helper::replaceMinusSign(number_format($net_value)) .' '. Helper::getCrDr($net_value),
            ];
        }
        return collect($data);
    }
    

    public function headings(): array
    {
        return [
            'Date', 
            'Transaction Id / Voucher No', 
            'Purpose', 
            'Debit', 
            'Credit', 
            'Closing Balance'
        ];
    }

    public function map($ledger): array
    {
        return [
            $ledger['Date'],
            $ledger['transaction_id'],
            $ledger['purpose'],
            $ledger['debit'],
            $ledger['credit'],
            $ledger['closing'],
        ];
    }
}

