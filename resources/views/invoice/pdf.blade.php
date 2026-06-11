<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Courier New", Courier, monospace;
            /* Monospace for thermal printer look */
        }

        .receipt {
            /* max-width: 320px; */
            border: 1px dashed #000;
            padding: 15px;
            margin: auto;
            background: #fff;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .table td,
        .table th {
            text-align: center;
            vertical-align: middle;
            font-size: 30px;
        }
        p{
           font-size: 40px;
           margin-bottom:0;
        }
        h5 {
            font-size: 50px;
        }

        .bold {
            font-weight: bold;
        }

        .amount-box {
            border: 1px solid #000;
            padding: 5px;
            font-size: 48px;
        }

        .amount-box td {
            text-align: center;
            font-size: 40px;
            padding: 2px;
        }

        .dotted-line {
            border-top: 1px dashed black;
            margin: 10px 0 10px;
        }

        .text-center {
            text-align: center;
        }
        
    </style>
</head>

<body>

    <div class="receipt">
        <div class="text-center">
            <p style="line-height:1 !important; margin-bottom:0 !important;">Jai Shree Ganesh</p>
            <p style="line-height:1 !important; margin-bottom:0 !important;">Jai Shree Krishna</p>
            <h5 class="fw-bold" style="font-size: 60px;">STANNY'S</h5>
            <p style="padding-bottom:25px;">LE MONDE DU LUXE</p>
        </div>

        <div class="dotted-line"></div>
        <div class="d-flex justify-content-between align-items-start">
            <!-- Left Column (Personal Info) -->
            <div class="col-12">
                <p style="text-align: left;"><strong>Mr/Mrs:</strong> {{ optional($data->customer)->name ?? 'N/A' }}</p>
                <p style="text-align: left;"><strong>Email:</strong> {{ optional($data->customer)->email ?? 'N/A' }}</p>
                <p style="text-align: left;">
                    <strong>Mobile No:</strong> 
                    {{ optional($data->customer)->country_code_phone ?? '' }} {{ optional($data->customer)->phone ?? 'N/A' }}
                </p>
                <p style="text-align: left;"><strong>Company Name:</strong> {{ optional($data->customer)->company_name ?? 'N/A' }}</p>
                <p style="text-align: left;"><strong>Address:</strong> {{ optional($data->order)->billing_address ?? $data->customer->location ?? 'N/A' }}</p>
            </div>

        </div> 
        
        
        <div class="dotted-line"></div>

        <table class="table table-sm table-bordered mt-3" style="table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: left !important; padding:7px;"><strong>Order</strong></th>
                    <th style="text-align:right !important; padding:7px;"><strong>Amount</strong></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $inv_amount = 0;
                @endphp
                @foreach ($invoice_payments as $key=> $payment)
                @php
                    $inv = App\Models\Invoice::where('id', $payment->invoice_id)->first();
                    $inv_amount += $payment->paid_amount;
                @endphp
                    <tr>
                        <td style="text-align:left; width:40%; padding:7px;">
                            {{ optional($inv?->order)->order_number ?? 'N/A' }} 
                         {{-- If $inv or $inv->order is null, show 'N/A' --}}
                        </td>
                        <td style="text-align:right; width:60%; padding:7px;">{{number_format($payment->paid_amount,2)}}</td>
                    </tr>
                @endforeach

                @if($data->collection_amount>$inv_amount)
                    <tr>
                        <td style="text-align:left; width:40%; padding:7px;"><strong>Advance Amount:</strong></td>
                        <td style="text-align:right; width:60%; padding:7px;"><strong>{{number_format($data->collection_amount-$inv_amount,2)}}</strong></td>
                    </tr>
                @endif
                <tr>
                    <td style="text-align:left; width:40%; padding:7px;"><strong>Total:</strong></td>
                    <td style="text-align:right; width:60%; padding:7px;"><strong>{{number_format($data->collection_amount,2)}}</strong></td>
                </tr>
                
            </tbody>
        </table>
        <div class="dotted-line"></div>
        <p class="text-center">Your mobile number has been successfully registered with STANNY'S.</p>

        {{-- <p class="bold">PIECES PURCHASED: {{ $totalQuantity }}</p> --}}

        <div class="dotted-line"></div>

        <p class="text-center">Thank you for shopping with us! </p>
    </div>
</body>

</html>
