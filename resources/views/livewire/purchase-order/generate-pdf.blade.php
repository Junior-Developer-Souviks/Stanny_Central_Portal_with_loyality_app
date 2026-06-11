<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<style>
    body{
        font-family: "Roboto", sans-serif;
        font-optical-sizing: auto;
        font-weight:400;
        font-style: normal;
        font-variation-settings:"wdth" 100;
    }
       table{
        width:100%;
        border-collapse: collapse;
    }

    h4, h1, h5, h2, h3, h6, p {
        margin-top: 0;
        color:#000;
    }
     .qty-col, .rate-col, .amount-col {
        width: 12%;        /* make smaller */
        text-align: center;
    }
    .product-table {
        margin-top: 20px;  /* push product table down */
    }
     .tfoot-value td:last-child {
        padding-left: 30px; /* gap between label & value */
    }
   
</style>
</head>
<body>
    {{-- Header --}}
<table style="width:100%; border:1px solid #000; border-collapse: collapse; margin-bottom:10px;">
    <tr>
        <!-- Logo -->
        <td style="width:25%; border:none;">
            <img src="{{ public_path('assets/img/logo.webp')}}" style="width:70px; height:auto;">
        </td>
        
        <!-- Title -->
        <td style="width:50%; text-align:center; font-weight:bold; font-size:18px; border:none;">
            PURCHASE ORDER
        </td>
        
        <!-- Order Details -->
        <td style="width:25%; text-align:right; border:none;">
            <div><strong>{{ $purchaseOrder->supplier ? $purchaseOrder->supplier->name : 'N/A' }}</strong></div>
            <div><strong>{{ $purchaseOrder->unique_id ?? 'N/A' }}</strong></div>
            <div>{{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>

{{-- Separate fabric & product --}}
@php
    $fabrics = $purchaseOrder->orderproducts->where('stock_type', 'fabric');
    $products = $purchaseOrder->orderproducts->where('stock_type', 'product');
@endphp

{{-- FABRIC TABLE --}}
@if($fabrics->count() > 0)
    <h4>Fabric Items</h4>
    <table style="width:100%; border:1px solid #000; border-collapse: collapse;">
        <thead style="background:#f5f5f5;">
            <tr>
                <th style="width:5%; border:1px solid #000; padding:6px;">#</th>
                <th class="name-col" style="border:1px solid #000; padding:6px;">Fabric Name</th>
                <th class="qty-col" style="border:1px solid #000; padding:6px;">Quantity (meter)</th>
                <th class="rate-col" style="border:1px solid #000; padding:6px;">Rate</th>
                <th class="amount-col" style="border:1px solid #000; padding:6px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $fabricTotal = 0; @endphp
            @foreach($fabrics as $index => $item)
                @php
                    $qty = $item->qty_in_meter;
                    $name = $item->fabric->pseudo_name ?? 'N/A';
                    $amount = $qty * $item->piece_price;
                    $fabricTotal += $amount;
                @endphp
                <tr>
                    <td  style="width:5%; border:1px solid #000; padding:6px;">{{ $index+1 }}</td>
                    <td class="name-col" style="border:1px solid #000; padding:6px;">{{ $name }}</td>
                    <td class="qty-col" style="border:1px solid #000; padding:6px;">{{ $qty }}</td>
                    <td class="rate-col" style="border:1px solid #000; padding:6px;">{{ number_format($item->piece_price, 2) }}</td>
                    <td class="amount-col" style="border:1px solid #000; padding:6px;">{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="tfoot-value">
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">FABRIC VALUE</td>
                <td style="font-weight:bold;"> {{ number_format($fabricTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endif

{{-- PRODUCT TABLE --}}
@if($products->count() > 0)
    <h4 style="margin-top:20px;">Product Items</h4>
    <table style="width:100%; border:1px solid #000; border-collapse: collapse;">
        <thead style="background:#f5f5f5;">
            <tr>
                <th style="width:5%; border:1px solid #000; padding:6px;">#</th>
                <th class="name-col" style="border:1px solid #000; padding:6px;">Product Name</th>
                <th class="qty-col" style="border:1px solid #000; padding:6px;">Quantity (pcs)</th>
                <th class="rate-col" style="border:1px solid #000; padding:6px;">Rate</th>
                <th class="amount-col" style="border:1px solid #000; padding:6px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $productTotal = 0; @endphp
            @foreach($products as $index => $item)
                @php
                    $qty = $item->qty_in_pieces;
                    $name = $item->product->name ?? 'N/A';
                    $amount = $qty * $item->piece_price;
                    $productTotal += $amount;
                @endphp
                <tr>
                    <td style="width:5%;border:1px solid #000; padding:6px;">{{ $index+1 }}</td>
                    <td class="name-col" style="border:1px solid #000; padding:6px;">{{ $name }}</td>
                    <td class="qty-col" style="border:1px solid #000; padding:6px;">{{ $qty }}</td>
                    <td class="rate-col" style="border:1px solid #000; padding:6px;">{{ number_format($item->piece_price, 2) }}</td>
                    <td class="amount-col" style="border:1px solid #000; padding:6px;">{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="tfoot-value">
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">PRODUCT VALUE</td>
                <td style="font-weight:bold;"> {{ number_format($productTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endif

{{-- GRAND TOTAL --}}
@if($fabrics->count() > 0 || $products->count() > 0)
    <table style="width:100%; border-collapse: collapse; margin-top:10px;">
        <tr>
            {{-- <td colspan="4" style="text-align:right; font-weight:bold;">TOTAL PO VALUE</td> --}}
            <td style="text-align:right; font-weight:bold; border:none;">
               TOTAL PO VALUE {{ number_format(($fabricTotal ?? 0) + ($productTotal ?? 0), 2) }}
            </td>
        </tr>
    </table>
@endif

</body>
</html