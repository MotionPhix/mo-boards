<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract #{{ $contract->contract_number }}</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 20px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 14px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .header .contract-info {
            margin: 10px 0 0 0;
            font-size: 16px;
            color: #666;
        }

        /* Company info */
        .company-info {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .company-info .left,
        .company-info .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }

        .company-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .company-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        /* Content area */
        .content {
            margin: 20px 0;
        }

        /* Contract details */
        .contract-details {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }

        .contract-details h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .detail-row {
            display: table;
            width: 100%;
            margin: 8px 0;
        }

        .detail-label,
        .detail-value {
            display: table-cell;
            padding: 5px;
        }

        .detail-label {
            width: 30%;
            font-weight: bold;
            color: #666;
        }

        .detail-value {
            width: 70%;
        }

        /* Billboards table */
        .billboards-section {
            margin: 25px 0;
        }

        .billboards-section h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        table {
            border-collapse: collapse;
            margin: 15px 0;
            width: 100%;
            border: 2px solid #333;
        }

        table td, table th {
            border: 1px solid #333;
            padding: 10px 12px;
            vertical-align: top;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* Signatures */
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-block {
            display: table-cell;
            width: 50%;
            padding: 20px;
            text-align: center;
        }

        .signature-line {
            border-bottom: 2px solid #333;
            width: 200px;
            margin: 30px auto 10px;
            height: 50px;
        }

        .signature-label {
            font-weight: bold;
            margin: 5px 0;
        }

        .signature-date {
            margin: 15px 0;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Print optimizations */
        @page {
            size: A4;
            margin: 20mm;
        }

        /* Text formatting */
        h1 {
            font-size: 22px;
            font-weight: bold;
            margin: 25px 0 15px 0;
            color: #333;
        }

        h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
        }

        h3 {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #333;
        }

        h4 {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #333;
        }

        p {
            margin: 10px 0;
            line-height: 1.6;
        }

        strong, b {
            font-weight: bold;
        }

        em, i {
            font-style: italic;
        }

        u {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BILLBOARD ADVERTISING CONTRACT</h1>
        <div class="contract-info">
            Contract #{{ $contract->contract_number }} | {{ ucfirst($contract->status) }}
        </div>
    </div>

    <div class="company-info">
        <div class="left">
            <h3>ADVERTISER (CLIENT)</h3>
            <p><strong>{{ $contract->client_name }}</strong></p>
            @if($contract->client_company)
                <p>{{ $contract->client_company }}</p>
            @endif
            @if($contract->client_address)
                <p>{{ $contract->client_address }}</p>
            @endif
            @if($contract->client_email)
                <p>Email: {{ $contract->client_email }}</p>
            @endif
            @if($contract->client_phone)
                <p>Phone: {{ $contract->client_phone }}</p>
            @endif
        </div>
        <div class="right">
            <h3>BILLBOARD COMPANY</h3>
            <p><strong>{{ $contract->company->name }}</strong></p>
            @if($contract->company->address)
                <p>{{ $contract->company->address }}</p>
            @endif
            @if($contract->company->email)
                <p>Email: {{ $contract->company->email }}</p>
            @endif
            @if($contract->company->phone)
                <p>Phone: {{ $contract->company->phone }}</p>
            @endif
        </div>
    </div>

    <div class="contract-details">
        <h3>CONTRACT DETAILS</h3>
        <div class="detail-row">
            <div class="detail-label">Contract Period:</div>
            <div class="detail-value">{{ $contract->start_date?->format('F j, Y') }} to {{ $contract->end_date?->format('F j, Y') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Duration:</div>
            <div class="detail-value">{{ $contract->start_date && $contract->end_date ? $contract->start_date->diffInMonths($contract->end_date) + 1 : 'N/A' }} month(s)</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Payment Terms:</div>
            <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $contract->payment_terms)) }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Monthly Amount:</div>
            <div class="detail-value">{{ $contract->currency ?? 'USD' }} {{ number_format($contract->monthly_amount, 2) }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Total Contract Value:</div>
            <div class="detail-value">{{ $contract->currency ?? 'USD' }} {{ number_format($contract->total_amount, 2) }}</div>
        </div>
        @if($contract->exchange_rate && $contract->exchange_rate != 1)
            <div class="detail-row">
                <div class="detail-label">Exchange Rate:</div>
                <div class="detail-value">{{ $contract->exchange_rate }}</div>
            </div>
        @endif
    </div>

    @if($contract->billboards && $contract->billboards->count() > 0)
        <div class="billboards-section">
            <h3>BILLBOARD LOCATIONS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Dimensions</th>
                        <th>Monthly Rate</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->billboards as $billboard)
                        <tr>
                            <td>{{ $billboard->code }}</td>
                            <td>{{ $billboard->name }}</td>
                            <td>{{ $billboard->location }}</td>
                            <td>
                                @if($billboard->width && $billboard->height)
                                    {{ $billboard->width }}' × {{ $billboard->height }}'
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $contract->currency ?? 'USD' }} {{ number_format($billboard->pivot?->rate ?? $billboard->monthly_rate, 2) }}</td>
                            <td>{{ $billboard->pivot?->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($contract->content || $contract->design)
        <div class="content">
            <h3>CONTRACT CONTENT</h3>
            {!! $contract->content ?? $contract->design ?? '' !!}
        </div>
    @endif

    @if($contract->notes)
        <div class="contract-details">
            <h3>ADDITIONAL NOTES</h3>
            <p>{{ $contract->notes }}</p>
        </div>
    @endif

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">CLIENT SIGNATURE</div>
            <div class="signature-date">Date: _________________</div>
            <p>{{ $contract->client_name }}</p>
            @if($contract->client_company)
                <p>{{ $contract->client_company }}</p>
            @endif
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">COMPANY REPRESENTATIVE</div>
            <div class="signature-date">Date: _________________</div>
            <p>{{ $contract->company->name }}</p>
            @if($contract->createdBy)
                <p>{{ $contract->createdBy->name }}</p>
            @endif
        </div>
    </div>

    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} | Contract #{{ $contract->contract_number }}
        @if($contract->company)
            | {{ $contract->company->name }}
        @endif
    </div>
</body>
</html>
