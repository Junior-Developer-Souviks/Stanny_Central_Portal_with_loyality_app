<!DOCTYPE html>
<html>

<head>
    <title>Page Title</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: "Roboto", sans-serif;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            font-variation-settings: "wdth" 100;
        }

        table {
            width: 100%;
            height: auto;
            border-collapse: collapse;
        }


        .table-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            z-index: 3;
            background-image: url("./assets/img/watermark-logo.png");
            background-position: 50% 100%;
            background-attachment: scroll;
            background-repeat: no-repeat;
            background-size: 60%;

        }



        .table-container table {
            flex-grow: 1;
            width: 100%;
            border-collapse: collapse;

        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 75px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: flex-end;

        }

        html,
        body {
            height: 100%;
        }



        h4,
        h1,
        h5,
        h2,
        h3,
        h6,
        p {
            margin-top: 0;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="table-container">
        <table class="table-custom">
            <tr>
                <td style="width:60%;">
                    <img src="{{  public_path('assets/img/pdf_logo.png')}}" style="width:210px; height:auto;">
                </td>
                <td style="width:40%;">
                    <h3 style="text-transform: uppercase; font-size: 15px; margin-bottom: 3px;">STE RADHEY'S SARL</h3>
                    <h3 style="text-transform: uppercase; font-size: 15px; margin-bottom: 3px;">CAPITAL: 1.000.000 FCFA
                    </h3>
                    <h3 style="text-transform: uppercase; font-size: 15px; margin-bottom: 3px;">NIU M24000000659298E
                    </h3>
                    <h3 style="font-size: 15px; margin-bottom: 3px;">RCCM CG-PNR-01-2024-B12-00203</h3>
                </td>
            </tr>
            {{-- <tr>
                <td colspan="2">
                    @php
                    $billing_address = $invoice->order->billing_address;
                    $formatted_address = preg_replace('/,\s*,/', ',', $billing_address);
                    $address_lines = explode(',', $formatted_address);
                    @endphp
                    <table>
                        <tr>
                            <td style="width:50%"></td>
                            <td style="width:50%">
                                @foreach($address_lines as $line)
                                <h3 style="text-transform: uppercase; font-size: 15px; margin-bottom: 3px;">{{
                                    trim($line) }}</h3>
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </td>
            </tr> --}}
            {{-- @dd($manualInvoice) --}}
            <tr>
                <td colspan="2">
                    <h2 style="font-size: 20px; color:#2d1e1e; font-weight: 400;">Invoice: INV/{{ date('Y') }}/{{
                        $manualInvoice->invoice_no }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom: 1px solid #ccc; padding-bottom: 25px;">
                    <table>
                        <tr>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Name:
                                </h5>
                                <p style="color:#000; word-break: break-word; white-space: normal; max-width: 200px;  font-size: 14px;">
                                    {{ucwords($manualInvoice->customer_name)}}
                                </p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Invoice
                                    Date:</h5>
                                <p style="color:#000; font-size: 14px;">{{
                                    \Carbon\Carbon::parse($manualInvoice->created_at)->format('d-m-Y') }}</p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Due Date:
                                </h5>
                                <p style="color:#000; font-size: 14px;">{{
                                    \Carbon\Carbon::parse($manualInvoice->due_date)->format('d-m-Y') }}</p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Source:
                                </h5>
                                <p style="color:#000; font-size: 14px;">{{ $manualInvoice->source }}</p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">
                                    Referrnce:</h5>
                                <p style="color:#000; font-size: 14px;">{{ $manualInvoice->reference }}</p>
                            </td>

                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <table>
                        <thead style="text-align: left;">
                            <th style="padding:8px 0; font-size: 14px; text-align: left;">Decriptions</th>
                            <th style="padding:8px 0; font-size: 14px; text-align: left;">Quantity</th>
                            <th style="padding:8px 0; font-size: 14px; text-align: left;">Unit Price</th>
                            {{-- <th style="padding:8px 0; font-size: 14px;">Taxes</th> --}}
                            <th style="padding:8px 0; font-size: 14px; text-align: left;">Total Price</th>
                        </thead>
                        <tbody>
                            @php
                            $totalQuantity = 0;
                            @endphp
                            @foreach($manualInvoice->items as $item)
                            @php
                            $totalQuantity += $item->quantity;
                            @endphp
                            <tr>
                                <td style="width:60%; line-height: 1.6; font-size: 13px;">{{ $item->product ?
                                    $item->product->name : "" }}</td>
                                <td style="font-size: 13px;">{{ $item->quantity }} set</td>
                                <td style="font-size: 13px;">{{ number_format( $item->unit_price,2 )
                                    }}</td>
                                {{-- <td style="font-size: 13px;">0.00</td> --}}
                                <td style="font-size: 13px;">{{ number_format( $item->total ) }} FCFA</td>
                            </tr>

                            @endforeach
                        </tbody>
                    </table>
                    <table style="margin-top: 45px;">
                        <tr>
                            <td style="width:50%;"></td>
                            <td>
                                <table style="border-top: 1px solid #ccc;">
                                    <tr>
                                        <td
                                            style="font-weight: 600; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            Total</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($manualInvoice->total_amount) }} FCFA</td>
                                    </tr>
                                    {{-- @php
                                    $tva = $item->total_price * 0.18;
                                    $ca = $tva * 0.05;
                                    $ht_amount = $item->total_price - ($tva + $ca);
                                    @endphp --}}

                                    <tr>
                                        <td
                                            style="color:#a2a0a0; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            H.T</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($manualInvoice->ht_amount, 2) }} FCFA</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="color:#a2a0a0; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            T.V.A</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($manualInvoice->tva_amount, 2) }}
                                            FCFA</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#a2a0a0; padding: 6px; font-size: 13px;">C.A</td>
                                        <td style="text-align: right; padding: 6px; font-size: 13px;">{{
                                            number_format($manualInvoice->ca_amount, 2) }}
                                            FCFA</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-size: 13px; padding: 6px; font-style: italic; border-bottom: 1px solid #ccc;">
                                            paid on {{
                                            \Carbon\Carbon::parse($manualInvoice->created_at)->format('d-m-Y') }}
                                            using cash</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($manualInvoice->paid_amount,2) }}
                                            FCFA</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px; font-weight: 600; font-size: 13px;">Amout Due</td>
                                        <td style="text-align: right; padding: 6px; font-size: 13px;">{{
                                            number_format($manualInvoice->due_amount) }} FCFA</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="font-size: 13px; margin-top: 20px;">Please use the following communication for your
                        payment: INV/{{ date('Y') }}/{{ $manualInvoice->invoice_no }}</p>
                </td>
            </tr>
        </table>
        <div class="footer">
            <table
                style="border-color:#000; border-style: double; border-bottom: 0; border-right: 0; border-left:0; margin-top: 35px;">
                <tr>
                    <td style="font-size: 13px; padding: 4px;">PNR: Lorem Ipsum is simply dummy text of the printing
                    </td>
                    <td style="font-size: 13px; padding: 4px;">Mobile: +148 15265978</td>
                    <td style="font-size: 13px; padding: 4px;">Email: info-pro@gmail.com</td>
                </tr>
                <tr>
                    <td style="font-size: 13px; padding: 4px;">PNR: Lorem Ipsum is simply dummy text of the printing
                    </td>
                    <td style="font-size: 13px; padding: 4px;">Mobile: +148 15265978</td>
                    <td style="font-size: 13px; padding: 4px;">Email: info-pro@gmail.com</td>
                </tr>
            </table>
        </div>
    </div>


</body>

</html