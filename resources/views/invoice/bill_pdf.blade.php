<!DOCTYPE html>
<html>

<head>
    <title>Page Title</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            background-image: url("./assets/img/stanny_full_page.png");
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

        /* .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 75px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: flex-end;

        } */
        .footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .footer strong {
            font-family: "Roboto", sans-serif;
            font-weight: 700;
        }

        .footer div {
            font-family: "Roboto", sans-serif;
            letter-spacing: 0.5px;
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
                    <img src="{{  public_path('assets/img/pdf_logo.png')}}"
                        style="width:250px; height:auto; margin-top:0px;">
                    <h2 style="font-size: 20px; color:#2d1e1e; font-weight: 400; margin-top:15px;">Order No: {{
                        $invoice->order->order_number }}</h2>
                </td>
                <td style="width:40%;">
                    <h3 
                        style="font-size: 15px; margin-bottom:5px;display:block; vertical-align:middle; line-height:15px;">
                        <span style="line-height: 0;">
                            <img src="{{public_path('assets/img/phone.svg')}}" alt=""
                                style="width: 14px; height:12px; display:inline-block;">
                        </span>
                        <span style="display:inline-block;"> (+242) 05 554 7777 </span>
                    </h3>
                    <h3
                        style="font-size: 15px; margin-bottom:9px;display:block; vertical-align:middle; line-height:15px;">
                        <span class="line-height: 0;">
                            <img src="{{public_path('assets/img/phone.svg')}}" alt=""
                                style="width: 14px; height:12px; display:inline-block;">
                        </span>
                        <span class="display:inline-block;">
                            (+242) 05 582 5555
                        </span>
                    </h3>
                    <h3
                        style="font-size: 15px; margin-bottom:5px;display:block; vertical-align:middle; line-height:15px;">
                        <span style="line-height: 0;">
                            <img src="{{public_path('assets/img/mail.svg')}}" alt=""
                                style="width: 12px; height:12px; display:inline-block;">
                        </span>
                        <span style="display:inline-block;">contact@stannys.com</span>
                    </h3>
                    <h3
                        style="font-size: 15px; margin-bottom:5px; display:block; vertical-align:middle; line-height:15px;">
                        <span style="line-height: 0;">
                            <img src="{{ public_path('assets/img/globe.svg')}}" alt=""
                                style="width: 12px; height:12px; display:inline-block;"
                                style="width: 14px; height:12px; display:inline-block;">
                        </span>
                        <span style="display:inline-block;">www.stannys.com</span>
                    </h3>
                    <h3 style="font-size: 15px; margin-bottom:5px;
                    display:inline-block; vertical-align:middle; line-height:15px; position:relative;">
                        <span style="line-height: 0; position: absolute; top:0; left:0;">
                            <img src="{{public_path('assets/img/map-pin.svg')}}" alt=""
                                style="width: 14px; height:14px; display:inline-block;">
                        </span>
                        <span style="display:block; padding-left:20px;">
                            18, Blv. Denis Sassou N'guesso,<br>
                            Brazzaville, République du Congo
                        </span>
                    </h3>
                    <div style="border: 1px solid #000; padding: 10px 10px 0px; border-radius: 5px; margin-top: 10px;">
                        <h3 style="font-size: 15px; margin-bottom:12px;
                            display:block; line-height:15px;">
                            <span style="line-height: 0;">
                                <img src="{{ public_path('assets/img/user.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span>
                                {{$invoice->customer ?
                                $invoice->customer->name : ""}}
                            </span>
                        </h3>
                        <h3 style="font-size: 15px; margin-bottom:12px;
                            display:block; vertical-align:middle; line-height:15px; position:relative;">
                            <span style="line-height: 0; position: absolute; top:0; left:0;">
                                <img src="{{ public_path('assets/img/map-pin.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:block; padding-left:20px;">
                                @if($invoice->customer &&
                                $invoice->customer->billingAddressLatest)
                                {{ $invoice->customer->billingAddressLatest->address }},
                                {{ $invoice->customer->billingAddressLatest->landmark ?
                                $invoice->customer->billingAddressLatest->landmark . ',' : '' }}
                                {{ $invoice->customer->billingAddressLatest->city }},
                                {{ $invoice->customer->billingAddressLatest->state }},
                                {{ $invoice->customer->billingAddressLatest->country }},
                                {{ $invoice->customer->billingAddressLatest->zip_code }}
                                @else
                                 N/A
                                @endif
                            </span>
                        </h3>
                        <h3 style="font-size: 15px;
                            display:block; vertical-align:middle; line-height:15px;">
                            <span style="line-height: 0;">
                                <img src="{{public_path('assets/img/phone.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:inline-block;">
                                @if($invoice->customer && $invoice->customer->phone)
                                 {{$invoice->customer->country_code_phone.' '.$invoice->customer->phone}}
                                @else
                                  N/A 
                                @endif
                            </span>
                        </h3>
                    </div>

                </td>
            </tr>
            <tr>
                <td colspan="2" style="vertical-align:top; margin-top:35px;">
                    {{--<h2 style="font-size: 20px; color:#2d1e1e; font-weight: 400;">Invoice No: INV/{{ date('Y') }}/{{
                        $invoice->invoice_no }}</h2>--}}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom: 1px solid #ccc; padding-bottom: 25px;">
                    <table>
                        <tr>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Order
                                    Date:
                                </h5>
                                <p
                                    style="color:#000; word-break: break-word; white-space: normal; max-width: 200px; font-size: 14px;">
                                    {{ \Carbon\Carbon::parse($invoice->order->created_at)->format('d-m-Y') }}
                                </p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Sales
                                    Person:</h5>
                                <p style="color:#000; font-size: 14px;">{{$invoice->user ? $invoice->user->name : ""}}
                                </p>
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
                            @if($invoice->order)
                            @foreach($invoice->order->items as $item)
                            @php
                            $totalQuantity += $item->quantity;
                            @endphp
                            <tr>
                                <td style="width:60%; line-height: 1.6; font-size: 13px;">{{ $item->product_name }}</td>
                                <td style="font-size: 13px;">{{ $item->quantity }} set</td>
                                <td style="font-size: 13px;">{{ number_format($item->piece_price)
                                    }}</td>
                                {{-- <td style="font-size: 13px;">0.00</td> --}}
                                <td style="font-size: 13px;">{{ number_format($item->quantity * $item->piece_price ) }} FCFA</td>
                            </tr>

                            @endforeach
                            @if ($invoice->order->air_mail > 0)
                            @php
                            $airMail = $invoice->order->air_mail;
                            @endphp
                            <tr>
                                <td style="width:60%; line-height: 1.6; font-size: 13px;">Air Mail</td>
                                <td style="font-size: 13px;">1</td>
                                <td style="font-size: 13px;">{{ number_format($airMail)}}</td>
                                {{-- <td style="font-size: 13px;">0.00</td> --}}
                                <td style="font-size: 13px;">{{ number_format( $airMail ) }} FCFA</td>
                            </tr>
                            @endif
                            @endif
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
                                            {{ number_format($invoice->net_price) }} FCFA</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div style="border-top: 1px solid #000; text-align: center; padding-top: 2px;">
                <div style="margin-top: -18px; font-weight: bold; text-transform: uppercase; font-size: 14px;">
                    STE RADHEY'S SARL
                </div>
                <div style="font-size: 13px; margin-top: 2px;">
                    CAPITAL: 1.000.000 FCFA<br>
                    NIU M24000000659298E<br>
                    RCCM: CG-BZV-01-2025-B20-00004
                </div>
                <div style="font-size: 12px; margin-bottom: 5px;">
                    <span style="margin-right: 15px;"><strong>Coordonnées Bancaires :</strong></span>
                    <span style="padding-left: 10px;">
                        <strong> BSCA Bank : 30020 88101 10125540000 08 </strong>
                    </span>
                </div>
            </div>
        </div>
</body>

</html