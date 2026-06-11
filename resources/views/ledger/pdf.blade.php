<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger Report</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            /* padding: 0; */
            font-size: 18px;
            padding:0px 15px 0px 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            
        }
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            font-size: 12px; /* Adjust font size for PDF */
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            text-transform: uppercase;
            font-size: 13px;
        }
        /* Page setup for PDF */
        @page {
            size: A4 portrait;
            /* margin: 5mm; */
            margin: 1mm;
        }

        /* Ensuring the content does not overflow */
        table {
            width: 100%;
            page-break-inside: avoid;
        }

        /* Styling for headers and page breaks */
        h2 {
            text-align: left;
            font-size: 16px;
            margin-bottom: 8px;
        }

        p {
            font-size: 12px;
        }

        /* Ensuring long text is wrapped properly */
        td, th {
            word-wrap: break-word;
        }

        /* Optional: Add page numbers or other footer elements */
        footer {
            text-align: center;
            font-size: 8px;
            position: fixed;
            bottom: 10mm;
            width: 100%;
        }
    </style>

</head>
<body>
  
    <h2>
        <strong>
            {{ucfirst($user_type) }} Name: {{  strtoupper($select_user_name ?? '........') }}
        </strong>
    </h2>
    <p><strong> Date Range: {{ date('d-m-Y', strtotime($from_date)) }} to {{ date('d-m-Y', strtotime($to_date)) }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Purpose</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Closing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledgers as $item)
                <tr>
                    <td>{{$item['Date']}}</td>
                    <td>{{$item['purpose']}}</td>
                    <td>{{$item['purpose_desc']}}</td>
                    <td>{{$item['debit']}}</td>
                    <td>{{$item['credit']}}</td>
                    <td>{{$item['closing']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>