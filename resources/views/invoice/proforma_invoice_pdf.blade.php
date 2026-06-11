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
                <td style="width:60%;" >
                    <img src="{{  public_path('assets/img/pdf_logo.png')}}" style="width:250px; height:auto;">
                    
                    <h2 style="font-size: 20px; color:#2d1e1e; font-weight: 400; margin-top:15px;">Proforma No: {{
                        $proforma->proforma_number }}</h2>
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
                            display:block; line-height:15px; position:relative;">
                            <span style="line-height: 0; position: absolute; top:0; left:0;">
                                <img src="{{ public_path('assets/img/user.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:inline-block; padding-left:17px;">
                                {{$proforma->customer ?
                                $proforma->customer->name : "N/A"}}
                            </span>
                        </h3>
                        <h3 style="font-size: 15px; margin-bottom:12px;
                            display:block; vertical-align:middle; line-height:15px; position:relative;">
                            <span style="line-height: 0; position: absolute; top:0; left:0;">
                                <img src="{{ public_path('assets/img/map-pin.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:inline-block; padding-left:17px;">
                                {{$proforma->customer ?
                                $proforma->customer->address : "N/A"}}
                            </span>
                        </h3>
                        <h3 style="font-size: 15px;
                            display:block; vertical-align:middle; line-height:15px; position:relative;">
                            <span style="line-height: 0; position:absolute; top:0; left:0;">
                                <img src="{{public_path('assets/img/phone.svg')}}" alt=""
                                    style="width: 14px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:inline-block; padding-left:17px;">
                                @if($proforma->customer && $proforma->customer->mobile)
                                  {{$proforma->customer->country_code.' '.$proforma->customer->mobile}}
                                @else
                                  N/A
                                @endif
                            </span>
                        </h3>
                        <h3 style="font-size: 15px;
                            display:block; vertical-align:middle; line-height:15px; position:relative;">
                            <span style="line-height: 0; position:absolute; top:0; left:0;">
                                <img src="{{public_path('assets/img/mail.svg')}}" alt=""
                                style="width: 12px; height:12px; display:inline-block;">
                            </span>
                            <span style="display:inline-block; padding-left:17px;">
                                @if($proforma->customer && $proforma->customer->email)
                                  {{$proforma->customer->email}}
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
                    {{--<h2 style="font-size: 20px; color:#2d1e1e; font-weight: 400;">proforma No: INV/{{ date('Y') }}/{{
                        $proforma->proforma_no }}</h2>--}}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom: 1px solid #ccc; padding-bottom: 25px;">
                    <table>
                        <tr>
                            
                            <td style="width:150px">
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;"> Date:</h5>
                                <p style="color:#000; font-size: 14px;">{{
                                    \Carbon\Carbon::parse($proforma->date)->format('d-m-Y') }}</p>
                            </td>
                            <td>
                                <h5 style="color:#000; font-size: 16px; font-weight: 600; margin-bottom: 6px;">
                                    No:</h5>
                                <p style="color:#000; font-size: 14px;">{{
                                   $proforma->proforma_number }}</p>
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
                            @foreach($proforma->items as $item)
                            @php
                            $totalQuantity += $item->quantity;
                            @endphp
                            <tr>
                                <td style="width:60%; line-height: 1.6; font-size: 13px;">{{ $item->product ? $item->product->name : ""}}</td>
                                <td style="font-size: 13px;">{{ $item->quantity }} set</td>
                                <td style="font-size: 13px;">{{ number_format( ($item->total_price)/($item->quantity) )
                                    }}</td>
                                {{-- <td style="font-size: 13px;">0.00</td> --}}
                                <td style="font-size: 13px;">{{ number_format( $item->total_price ) }} FCFA</td>
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
                                            Montant</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($proforma->total_amount) }} FCFA</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-weight: 600; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            Montant Finale</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($proforma->total_amount) }} FCFA</td>
                                    </tr>
                                    @php
                                        $tvaPercentage = floatval(env('TVA_PERCENTAGE'));
                                        $caPercentage = floatval(env('CA_PERCENTAGE'));
                                        $tva = $item->total_price * ($tvaPercentage/100);
                                        $ca = $tva * ($caPercentage/100);
                                        // $ht_amount = $item->total_price - ($tva + $ca);
                                    @endphp
                                    <tr>
                                        <td
                                            style="color:#130404; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            T.V.A</td>
                                        <td
                                            style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">
                                            {{ number_format($tva, 2) }}
                                            FCFA</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#130404; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">C.A</td>
                                        <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ccc; font-size: 13px;">{{
                                            number_format($ca, 2) }}
                                            FCFA</td>
                                    </tr>
                                    
                                    <tr>
                                        <td style="padding: 6px; font-weight: 600; font-size: 13px;">TOTAL</td>
                                        <td style="text-align: right; padding: 6px; font-size: 13px;">{{
                                            number_format($proforma->total_amount + $tva + $ca ,2) }} FCFA</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
        </table>
        @if (!empty($proforma->conditions))
            <div style="margin-top: 40px; font-size: 13px; color: #000;">
                <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; text-decoration: underline;">Condition de Vente :</h4>
                <div style="font-size: 13px; line-height: 1.6;">
                    {!! $proforma->conditions !!}
                </div>
            </div>
        @endif
        <div class="footer" style="margin-top: 30px;">
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