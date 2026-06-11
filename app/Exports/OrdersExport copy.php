<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;


class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public $customer_id, $created_by, $start_date, $end_date, $search;

    public function __construct($customer_id = null, $created_by = null, $start_date = null, $end_date = null, $search = null)
    {
        $this->customer_id = $customer_id;
        $this->created_by = $created_by;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->search = $search;
    }

    public function query()
    {
        return Order::query()
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_email', 'like', '%' . $this->search . '%');
            })
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
            })
            ->when($this->created_by, function ($query) {
                $query->where('created_by', $this->created_by);
            })
            ->when($this->start_date, function ($query) {
                $query->whereDate('created_at', '>=', $this->start_date);
            })
            ->when($this->end_date, function ($query) {
                $query->whereDate('created_at', '<=', $this->end_date);
            })
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Business Type', 'Customer ID', 'Created By', 'Order Number', 'Customer Name', 'Email',
            'Billing Address', 'Shipping Address', 'Total Amount', 'Paid Amount', 'Remaining Amount',
            'Last Payment Date', 'Payment Mode', 'Status', 'Business Type', 'Created At'
        ];
    }


    public function map($order): array
    {
        return [
            $order->id,
            $order->business_type,
            $order->customer_id,
            $order->created_by,
            $order->order_number,
            $order->customer_name,
            $order->customer_email,
            $order->billing_address,
            $order->shipping_address,
            number_format($order->total_amount, 2),
            number_format($order->paid_amount, 2),
            number_format($order->remaining_amount, 2),
            $order->last_payment_date ? Carbon::parse($order->last_payment_date)->format('Y-m-d') : 'N/A',
            $order->payment_mode,
            $order->status,
            $order->created_at ? Carbon::parse($order->created_at)->format('Y-m-d H:i:s') : 'N/A',
        ];
    }
    
}
