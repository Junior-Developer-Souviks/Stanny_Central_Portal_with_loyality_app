<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->unique_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; font-size: 20px; font-weight: bold; padding: 20px 0; border-bottom: 2px solid black; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid black; padding: 10px; text-align: left; }
        .section-title { background: #f2f2f2; padding: 10px; font-weight: bold; margin-top: 20px; border-bottom: 2px solid black; }
    </style>
</head>
<body>
    <div class="header">Purchase Order</div>

    <table width="100%">
        <tr>
            <td><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</td>
            <td align="right"><strong>PO ID:</strong> {{ $purchaseOrder->unique_id }}</td>
        </tr>
        <tr>
            <td><strong>Date:</strong> {{ date('d/m/Y', strtotime($purchaseOrder->created_at)) }}</td>
        </tr>
    </table>
    @if ($purchaseOrder->status == 0)
    
        @if ($purchaseOrder->orderproducts->where('stock_type', 'product')->count() > 0)
        <!-- PRODUCTS TABLE -->
        <div class="section-title">Products</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; $totalProducts = 0; @endphp

                @foreach ($purchaseOrder->orderproducts->where('stock_type', 'product') as $item)
                    @php $amount = $item->qty_in_pieces * $item->piece_price; $totalProducts += $amount; @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->qty_in_pieces  }}</td>
                        <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                        <td>Rs. {{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" align="right"><strong>Total Product Value:</strong></td>
                    <td><strong>Rs. {{ number_format($totalProducts, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        @if ($purchaseOrder->orderproducts->where('stock_type', 'fabric')->count() > 0 )
            <!-- FABRICS TABLE -->
            <div class="section-title">Fabrics</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $j = 1; $totalFabrics = 0; @endphp
                    @foreach ($purchaseOrder->orderproducts->where('stock_type', 'fabric') as $item)
                        @php $amount = $item->qty_in_meter * $item->piece_price; $totalFabrics += $amount; @endphp
                        <tr>
                            <td>{{ $j++ }}</td>
                            <td>{{ $item->fabric_name }}</td>
                            <td>{{ $item->qty_in_meter }}</td>
                            <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                            <td>Rs. {{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" align="right"><strong>Total Fabric Value:</strong></td>
                        <td><strong>Rs. {{ number_format($totalFabrics, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            <h3 align="right">Grand Total: Rs. {{ number_format($totalProducts + $totalFabrics, 2) }}</h3>

        @endif
        {{-- after generate the grn else part will work --}}
    @else
        @if ($purchaseOrder->orderproducts->where('stock_type', 'product')->count() > 0)

            <!-- PRODUCTS TABLE -->
            <div class="section-title">Products</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Order Quantity</th>
                        <th>GRN Quantity</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; $totalProducts = 0; @endphp
                    @foreach ($purchaseOrder->orderproducts->where('stock_type', 'product') as $item)
                        @php $amount = $item->qty_while_grn_product * $item->piece_price; $totalProducts += $amount; @endphp
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->qty_in_pieces  }}</td>
                            <td>{{ intval($item->qty_while_grn_product)  }}</td>
                            <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                            <td>Rs. {{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" align="right"><strong>Total Product Value:</strong></td>
                        <td><strong>Rs. {{ number_format($totalProducts, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if ($purchaseOrder->orderproducts->where('stock_type', 'fabric')->count() > 0 )

        <!-- FABRICS TABLE -->
        <div class="section-title">Fabrics</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Order Quantity</th>
                    <th>GRN Quantity</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                
                @php $j = 1; $totalFabrics = 0;
                    $i = 1; $totalProducts = 0;
                @endphp
                @foreach ($purchaseOrder->orderproducts->where('stock_type', 'fabric') as $item)
                    @php $amount = $item->qty_while_grn_fabric * $item->piece_price; $totalFabrics += $amount; @endphp
                    <tr>
                        <td>{{ $j++ }}</td>
                        <td>{{ $item->fabric_name }}</td>
                        <td>{{ intval($item->qty_in_meter) }}</td>
                        <td>{{ intval($item->qty_while_grn_fabric) }}</td>
                        <td>Rs. {{ number_format($item->piece_price, 2) }}</td>
                        <td>Rs. {{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5" align="right"><strong>Total Fabric Value:</strong></td>
                    <td><strong>Rs. {{ number_format($totalFabrics, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <h3 align="right">Grand Total: Rs. {{ number_format($totalProducts + $totalFabrics, 2) }}</h3>
        @endif
    @endif
    <!-- GRAND TOTAL -->
</body>
</html>
