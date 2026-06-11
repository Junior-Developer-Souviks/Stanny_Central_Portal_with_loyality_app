<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .invoice-header { text-align: center; margin-bottom: 20px; }
        .invoice-details { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="invoice-header">
    <h1>Invoice #{{ $invoice->invoice_no }}</h1>
    <p>Order ID: {{ $invoice->order->id }}</p>
    <p>Date: {{ \Carbon\Carbon::now()->toFormattedDateString() }}</p>
</div>

<div class="invoice-details">
    <p><strong>Customer:</strong> {{ $invoice->customer->name }}</p>
    <p><strong>Staff:</strong> {{ $invoice->user->name }}</p>
    <p><strong>Packingslip No:</strong> {{ $invoice->packing->slipno }}</p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total Amount:</strong> {{ number_format($invoice->net_price, 2) }}</p>
    <p><strong>Required Payment Amount:</strong> {{ number_format($invoice->required_payment_amount, 2) }}</p>
</div>

</body>
</html>

