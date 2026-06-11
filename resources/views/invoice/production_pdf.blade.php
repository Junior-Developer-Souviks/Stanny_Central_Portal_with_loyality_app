<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        h3 {
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
            background: #fcfcfc;
        }

        .section-title {
            font-weight: bold;
            background: #eee;
            padding: 4px 8px;
            margin: 10px 0 5px;
        }

        .highlight {
            color: red;
            font-weight: bold;
        }

        /* ── Page-break wrapper ── */
        .item-block {
            page-break-before: always;
        }

        .item-block:first-child {
            page-break-before: auto;
        }

        /* Keep the order-info header together on its page */
        .order-info {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- ── Order Header ── --}}
        <div class="order-info">
            <table width="100%" cellpadding="10" cellspacing="0"
                   style="margin-bottom: 20px; border: 1px solid #ccc;">
                <tr valign="top">
                    <td width="50%" style="border-right: 1px solid #ccc;">
                        <h3 style="margin-top: 0;">Order Information</h3>
                        <table cellpadding="4">
                            <tr>
                                <td><strong>Order Id:</strong></td>
                                <td>{{config('app.order_prefix') }}{{ $order->order_number ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Previous Order Id:</strong></td>
                                <td>{{config('app.order_prefix') }}{{ $previousOrder->order_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Order Time:</strong></td>
                                <td>{{ $order->created_at->format('d M Y h:i A') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ── One block per item, each on its own page (after the first) ── --}}
        @if ($orderItems->isNotEmpty())
            @foreach ($orderItems as $loopIndex => $item)

                {{-- First item stays on the header page; every subsequent item forces a new page --}}
                <div class="{{ $loopIndex === 0 ? '' : 'item-block' }}">

                    <h3>Order Item #{{ $loopIndex + 1 }}</h3>

                    {{-- ── Item summary row ── --}}
                    <table class="table" width="100%" style="border: 1px solid #ccc;">
                        <thead>
                            <tr>
                                <th width="20%">Collection</th>
                                <th width="40%">Product</th>
                                <th width="15%">Qty</th>
                                <th width="25%">Expected Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $item['collection_title'] }}</td>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>
                                    <strong>
                                        {{ !empty($item['expected_delivery_date']) 
                                            ? \Carbon\Carbon::parse($item['expected_delivery_date'])->format('F, Y') 
                                            : 'N/A' }}
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- ── Garment detail box ── --}}
                    @if ($item['collection_id'] == 1)
                        <div class="info-box">

                            <p><strong>Fabric: {{ $item['fabrics']->title ?? 'N/A' }}</strong></p>
                            <p>
                                <strong>Catalogue:</strong>
                                {{ optional(optional($item['catalogue'])->catalogueTitle)->title ?? 'N/A' }}
                                (Page: {{ $item['cat_page_number'] ?? 'N/A' }})
                            </p>

                            @if (!empty($item['remarks']))
                                <p><strong>Remark:</strong> {{ $item['remarks'] }}</p>
                            @endif

                            @if (!empty($item['catlogue_image']['image_path']))
                                <p><strong>Catalogue Image:</strong></p>
                                <img src="{{ asset('storage/' . $item['catlogue_image']['image_path']) }}"
                                     style="width:150px; height:150px; border:1px solid #ccc; border-radius:4px;"
                                     alt="Catalogue Image">
                            @endif

                            @if (!empty($item['voice_remark']['voices_path']))
                                <p><strong>Voice Remarks:</strong></p>
                                <audio controls>
                                    <source src="{{ asset('storage/' . $item['voice_remark']['voices_path']) }}"
                                            type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            @endif

                            {{-- ── Measurements ── --}}
                            <div class="section-title">Measurements</div>
                            @php
                                $measurements = collect($item['measurements'])
                                    ->mapWithKeys(function ($m) {
                                        return [
                                            $m['measurement_name'] . ' [' . $m['measurement_title_prefix'] . ']' => [
                                                'value' => $m['measurement_value'],
                                                'remarks' => $m['remarks'] ?? null,
                                            ]
                                        ];
                                    });

                                $chunks = array_chunk($measurements->toArray(), 5, true);
                            @endphp

                            <table width="100%" cellspacing="0" cellpadding="6">
                                @foreach ($chunks as $row)
                                    <tr>
                                        @foreach ($row as $label => $data)
                                            <td style="padding:8px; vertical-align:top;">
                                                <div style="display:flex; flex-direction:column; justify-content:space-between;">
                                                    <div style="font-size:11px; font-weight:bold; margin-bottom:3px;">
                                                        {{ $label }}
                                                    </div>
                                                    <div style="border:1px solid #ccc; padding:6px; background:#fff;
                                                                font-size:12px; border-radius:4px; min-height:25px; text-align:center;">
                                                        {{ $data['value'] }}
                                                    </div>
                                                    @if(!empty($data['remarks']))
                                                         <div style="margin-top:5px; padding:5px; font-size:10px; color:#555;
                                                            border:1px solid #ddd; border-radius:4px; background:#f9f9f9;
                                                            text-align:left;">
                                                            {{ $data['remarks'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                        @for ($i = count($row); $i < 5; $i++)
                                            <td></td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </table>

                            {{-- ── Extra Requirements ── --}}
                            <div class="section-title">Extra Requirements</div>
                            @php
                              $handStitching = null;

                                    if (in_array('mens_jacket_suit', $item['extra_type'] ?? [])) {
                                        $handStitching = $item['mens_hand_stitching'] ?? null;
                                    }
                                
                                    if (in_array('ladies_jacket_suit', $item['extra_type'] ?? [])) {
                                        $handStitching = $item['ladies_hand_stitching'] ?? null;
                                    }

                                $extraFields = [
                                    'Fittings'              => $item['fittings']              ?? null,
                                    'Priority Level'        => $item['priority']               ?? null,
                                    'Shoulder Type'         => $item['shoulder_type']          ?? null,
                                    'Hand Stitching'         => $handStitching   ?? null,
                                    'Vents'                 => $item['vents']                  ?? null,
                                    'Vents Required'        => $item['vents_required']         ?? null,
                                    'Vents Count'           => $item['vents_count']            ?? null,
                                    'Fold Cuff Required'    => $item['fold_cuff_required']     ?? null,
                                    'Fold Cuff Size'        => $item['fold_cuff_size']         ?? null,
                                    'Pleats Required'       => $item['pleats_required']        ?? null,
                                    'Back Pocket Required'  => $item['back_pocket_required']   ?? null,
                                    'Adjustable Belt'       => $item['adjustable_belt']        ?? null,
                                    'Suspender Button'      => $item['suspender_button']       ?? null,
                                    'Trouser Position'      => $item['trouser_position']       ?? null,
                                    'Client Name Required'  => $item['client_name_required']   ?? null,
                                    'Client Name'           => $item['client_name_place']      ?? null,
                                    'Client Name Options'   => $item['client_name_options']    ?? null,
                                ];

                                $extraFiltered = array_filter($extraFields, fn($v) => !empty($v));
                                 // 4 items per row
                                 $chunks = array_chunk($extraFiltered, 4, true);
                            @endphp

                            @if (!empty($extraFiltered))
                                <table width="100%" class="table table-bordered" style="font-size: 11px;">
                                    @foreach ($chunks as $chunk)
                                        <tr>
                                            @foreach ($chunk as $label => $value)
                                                <td style="padding:4px;">
                                                    <strong>{{ $label }}:</strong> {{ $value }}
                                                </td>
                                            @endforeach
                            
                                            {{-- Empty cells if row has less than 4 items --}}
                                            @for ($i = count($chunk); $i < 4; $i++)
                                                <td></td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <p>No extra details provided.</p>
                            @endif

                        </div>{{-- /.info-box --}}
                    @endif

                </div>{{-- /.item-block --}}

            @endforeach
        @endif

    </div>
</body>

</html>