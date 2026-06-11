<div>
    <style>
        .invoice-title h2, .invoice-title h3 {
            display: inline-block;
        }

        .table > tbody > tr > .no-line {
            border-top: none;
        }

        .table > thead > tr > .no-line {
            border-bottom: none;
        }

        .table > tbody > tr > .thick-line {
            border-top: 2px solid;
        }
    </style>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="printArea">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="invoice-title">
                                <h2>Invoice</h2><h3 class="pull-right"># {{$order->order_number}}</h3>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address class="w-50">
                                    <strong>Billed To:</strong><br>
                                        {{$order->customer_name}}<br>
                                        {{$order->customer_email}}<br>
                                        {{$order->billing_address}}<br>
                                    </address>
                                </div>
                                <div class="col-md-6">
                                    <address class="text-end">
                                    <strong>Shipped To:</strong><br>
                                        {{$order->customer_name}}<br>
                                        {{$order->customer_email}}<br>
                                        {{$order->shipping_address}}<br>
                                    </address>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <address>
                                        <strong>Payment Method:</strong><br>
                                        {{$order->payment_mode}}
                                    </address>
                                </div>
                                <div class="col-xs-6 text-end">
                                    <address>
                                        <strong>Order Date:</strong><br>
                                        {{date('M d, Y',strtotime($order->created_at))}}<br><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><strong>Order summary</strong></h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <td><strong>Type</strong></td>
                                                    <td><strong>Fabric</strong></td>
                                                    <td><strong>Product</strong></td>
                                                    <td class="text-end"><strong>Totals</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- foreach ($order->lineItems as $line) or some such thing here -->
                                            
                                                @forelse($order->items as $key => $item)
                                                    <tr>
                                                        <td>{{ $item->collectionType && is_object($item->collectionType) ? $item->collectionType->title : 'N/A' }}</td> <!-- Display the product code or 'N/A' if not available -->
                                                        <td>{{ $item->fabrics?$item->fabrics: 'N/A' }}</td> <!-- Display the product code or 'N/A' if not available -->
                                                        <td>{{ $item->product_name?$item->product_name: 'N/A' }}</td> <!-- Display the product code or 'N/A' if not available -->
                                                        <td class="text-end">{{ number_format($item->price, 2) }}</td> <!-- Calculate total price -->
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No items found for this order.</td> <!-- Display a fallback message -->
                                                    </tr>
                                                @endforelse
                                            
                                                <tr>
                                                    <td class="thick-line"></td>
                                                    <td class="thick-line"></td>
                                                    <td class="thick-line text-center"><strong>Total Amount</strong></td>
                                                    <td class="thick-line text-end">{{ number_format($order->total_amount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="no-line"></td>
                                                    <td class="no-line"></td>
                                                    <td class="no-line text-center"><strong>Paid Amount</strong></td>
                                                    <td class="no-line text-end">{{ number_format($order->paid_amount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="no-line"></td>
                                                    <td class="no-line"></td>
                                                    <td class="no-line text-center"><strong>Remaining Amount</strong></td>
                                                    <td class="no-line text-end {{$order->remaining_amount>0?"text-danger":""}}">{{ number_format($order->remaining_amount, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <!-- Button to trigger print -->
                   <button class="btn btn-primary" onclick="printDiv('printArea')">Print</button>
               </div>
            </div>
            
        </div>
    </div>
</div>
<script>
    function printDiv(divId) {
        var content = document.getElementById(divId).innerHTML;
        var printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write('<html><head><title>Print</title></head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>

