<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Product Delivery - {{ $order_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 20px;
        }

        /* ── INFO TABLE ─────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: top;
        }

        .info-table .label-col {
            width: 13%;
            font-weight: bold;
            background-color: #f0f0f0;
            white-space: nowrap;
        }

        .info-table .value-col {
            width: 20%;
        }

        .info-table .right-label {
            width: 10%;
            font-weight: bold;
            background-color: #f0f0f0;
            white-space: nowrap;
        }

        .info-table .right-value {
            width: 57%;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Header row for "Field / Name" labels */
        .info-table .header-row td {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: left;
        }

        /* ── ITEMS TABLE ────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 5px 7px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
            font-size: 10px;
        }

        .items-table .item-name   { width: 18%; font-weight: bold; }
        .items-table .order-status{ width: 20%; }
        .items-table .order-date  { width: 14%; }
        .items-table .deliv-date  { width: 14%; }
        .items-table .deliv-sign  { width: 20%; }
        .items-table .amount      { width: 14%; text-align: right; }
        .items-table .received-at {
            width: 14%;
        }

        /* ── TOTAL / PAYMENT ROWS ───────────────────── */
        .items-table .summary-label {
            text-align: right;
            font-weight: bold;
            background-color: #fafafa;
        }

        .items-table .summary-amount {
            text-align: right;
            font-weight: bold;
        }

        /* ── EMPTY SIGNATURE ROWS ───────────────────── */
        .items-table .empty-row td {
            height: 22px;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .payment-table th {
            border: 1px solid #000;
            padding: 6px;
            background-color: #f0f0f0;
            font-size: 10px;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
        }
        
        .payment-table td {
            border: 1px solid #000;
            padding: 6px;
            height: 28px;
            font-size: 10px;
        }
        
        .payment-table .col-date {
            width: 18%;
        }
        
        .payment-table .col-pay {
            width: 18%;
        }
        
        .payment-table .col-tot-rest {
            width: 20%;
        }
        
        .payment-table .col-act-rest {
            width: 20%;
        }
        
        .payment-table .col-signature {
            width: 24%;
        }

        /* ── STATUS BADGE ───────────────────────────── */
        .status-pass        { color: #000; }
        .status-hold        { font-weight: bold; color: #000; }
        .status-pending     { font-weight: bold; color: #8a6000; }   /* amber  */
        .status-transit     { font-weight: bold; color: #005fa3; }   /* blue   */
        .status-alteration  { font-weight: bold; color: #7a0099; }   /* purple */
        .status-rejected    { font-weight: bold; color: #c00000; }   /* red    */
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════ --}}
    {{--  SECTION 1 – INFO TABLE                    --}}
    {{-- ═══════════════════════════════════════════ --}}
   
    <table class="info-table">

    <tr class="header-row">

        
        <td class="label-col">Order No.</td>
        <td class="value-col">{{ env('ORDER_PREFIX').$order_no }}</td>
        
        <td class="right-label">Name</td>

        <td class="right-value">{{ strtoupper($name) }}</td>

       

    </tr>

    {{-- Order No / Rank --}}
    <tr>
       <td class="label-col">Previous Order</td>
        <td class="value-col">{{ $last_order_no ? env('ORDER_PREFIX').$last_order_no : "" }}</td>
        
        <td class="right-label">Rank</td>
        <td class="right-value">{{ strtoupper($rank) }}</td>
    </tr>

    {{-- Previous Order / Address --}}
    <tr>
        
        
        <td class="label-col">Next Order No</td>
        <td class="value-col">{{ $next_order_no ? env('ORDER_PREFIX').$next_order_no : '' }}</td>
        
          <td class="right-label">Company</td>
          <td class="right-value">{{ strtoupper($company_name ?? '') }}</td>
       
    </tr>
    
    <tr>
         <td class="label-col"></td>
        <td class="value-col"></td>
        
         <td class="right-label">Address</td>
        <td class="right-value">{{ strtoupper($address) }}</td>
    </tr>

    {{-- Next Order / Telephone --}}
    <tr>
        
         <td class="label-col"></td>
        <td class="value-col"></td>
        
        <td class="right-label">Telephone</td>
        <td class="right-value">{{ $telephone }}</td>
    </tr>

</table>

    {{-- ═══════════════════════════════════════════ --}}
    {{--  SECTION 2 – ITEMS + TOTALS TABLE          --}}
    {{-- ═══════════════════════════════════════════ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="item-name">Item Name</th>
                <th class="order-status">Order Status</th>
                <th class="received-at">Received At</th>
                <th class="deliv-date">Delivery Date</th>
                <th class="deliv-sign">Delivery Signature</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
          {{--  @dd($items)  --}}
            {{-- ── Per-Item Rows ─────────────────────── --}}
            @foreach($items as $item)
            <tr>
                <td class="item-name">{{ $item['name'] }}</td>
                <td class="order-status">
                    @php
                        $st = $item['status'];
                        if ($st === 'Pass') {
                            $stClass = 'status-pass';
                        } elseif (str_starts_with($st, 'Pending')) {
                            $stClass = 'status-pending';
                        } elseif (str_starts_with($st, 'Hold/Pass')) {
                            $stClass = 'status-hold';
                        } elseif (str_starts_with($st, 'In Transit') || $st === 'Received by Sales Team') {
                            $stClass = 'status-transit';
                        } elseif ($st === 'Alteration Required') {
                            $stClass = 'status-alteration';
                        } elseif ($st === 'Rejected') {
                            $stClass = 'status-rejected';
                        } else {
                            $stClass = 'status-pending';
                        }
                    @endphp
                    <span class="{{ $stClass }}">{{ $st }}</span>
                </td>
                 <td class="received-at">
                    {{ $item['received_at_salesman'] ?? '' }}
                </td>
                <td class="deliv-date">{{ $item['delivery_date'] ?? '' }}</td>
                <td class="deliv-sign"></td>
                <td class="amount"></td>
            </tr>
            @endforeach

            <!--{{-- ── Grand Total ───────────────────────── --}}-->
            <!--<tr>-->
            <!--    <td colspan="4" class="summary-label">Grand Total</td>-->
            <!--    <td class="deliv-sign" colspan="1"></td>-->
            <!--    <td class="summary-amount" colspan="4">{{ $amount }}</td>-->
            <!--</tr>-->

            <!--{{-- ── Payment Rows ──────────────────────── --}}-->
            <!--@foreach($paymentRows as $row)-->
            <!--<tr>-->
            <!--    <td colspan="4" class="summary-label">-->
            <!--        Pay-->
            <!--        @if(!empty($row['date']))-->
            <!--            <span style="font-weight:normal;font-size:9px;">({{ $row['date'] }})</span>-->
            <!--        @endif-->
            <!--    </td>-->
            <!--    <td class="deliv-sign"></td>-->
            <!--    <td class="summary-amount" colspan="4">{{ $row['pay'] }}</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--    <td colspan="4" class="summary-label">Total Rest</td>-->
            <!--    <td class="deliv-sign">{{ $row['signature'] ?? '' }}</td>-->
            <!--    <td class="summary-amount" colspan="4">{{ $row['total_rest'] }}</td>-->
            <!--</tr>-->
            <!--@endforeach-->

            <!--{{-- If no payments yet, show empty Pay / Total Rest rows --}}-->
            <!--@if(empty($paymentRows))-->
            <!--<tr>-->
            <!--    <td colspan="4" class="summary-label">Pay</td>-->
            <!--    <td class="deliv-sign"></td>-->
            <!--    <td class="summary-amount"></td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--    <td colspan="4" class="summary-label">Total Rest</td>-->
            <!--    <td class="deliv-sign"></td>-->
            <!--    <td class="summary-amount" colspan="2">{{ $amount }}</td>-->
            <!--</tr>-->
            <!--@endif-->
            {{-- ── Grand Total ───────────────────────── --}}
            <tr>
                <td colspan="4" class="summary-label">Grand Total</td>
                <td class="deliv-sign"></td>
                <td class="summary-amount">{{ $amount }}</td>
            </tr>
            
            {{-- ── Payment Rows ──────────────────────── --}}
            @foreach($paymentRows as $row)
            
            <tr>
                <td colspan="4" class="summary-label">
                    Pay
                    @if(!empty($row['date']))
                        <span style="font-weight:normal;font-size:9px;">
                            ({{ $row['date'] }})
                        </span>
                    @endif
                </td>
            
                <td class="deliv-sign"></td>
            
                <td class="summary-amount">
                    {{ $row['pay'] }}
                </td>
            </tr>
            
            <tr>
                <td colspan="4" class="summary-label">
                    Total Rest
                </td>
            
                <td class="deliv-sign">
                    {{ $row['signature'] ?? '' }}
                </td>
            
                <td class="summary-amount">
                    {{ $row['total_rest'] }}
                </td>
            </tr>
            
            @endforeach
            
            {{-- If no payments yet --}}
            @if(empty($paymentRows))
            
            <tr>
                <td colspan="4" class="summary-label">Pay</td>
                <td class="deliv-sign"></td>
                <td class="summary-amount"></td>
            </tr>
            
            <tr>
                <td colspan="4" class="summary-label">Total Rest</td>
                <td class="deliv-sign"></td>
                <td class="summary-amount">{{ $amount }}</td>
            </tr>
            
            @endif
            
             </tbody>
        </table>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-pay">Pay</th>
                        <th class="col-tot-rest">Tot. Rest</th>
                        <th class="col-act-rest">Act. Rest</th>
                        <th class="col-signature">Signature</th>
                    </tr>
                </thead>
            <tbody>
            {{-- ── Empty Signature Rows ──────────────── --}}
            @for($i = 0; $i < 8; $i++)
            <tr class="empty-row">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor

        </tbody>
    </table>

</body>
</html>