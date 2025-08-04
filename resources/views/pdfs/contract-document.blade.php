<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Document #{{ $contract->contract_number }}</title>
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

        /* Contract meta info */
        .contract-meta {
            display: table;
            width: 100%;
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
        }

        .contract-meta .left,
        .contract-meta .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .contract-meta h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .contract-meta p {
            margin: 5px 0;
            font-size: 14px;
        }

        /* Main content area */
        .content {
            margin: 30px 0;
            min-height: 400px;
        }

        .content-wrapper {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #dee2e6;
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

        /* Text formatting for content */
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

        ul, ol {
            margin: 10px 0;
            padding-left: 30px;
        }

        li {
            margin: 5px 0;
        }

        blockquote {
            margin: 15px 0;
            padding-left: 20px;
            border-left: 4px solid #ccc;
            font-style: italic;
        }

        table {
            border-collapse: collapse;
            margin: 15px 0;
            width: 100%;
            border: 1px solid #333;
        }

        table td, table th {
            border: 1px solid #333;
            padding: 8px 12px;
            vertical-align: top;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* Image handling */
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRACT DOCUMENT</h1>
        <div class="contract-info">
            Contract #{{ $contract->contract_number }} | {{ ucfirst($contract->status) }}
        </div>
    </div>

    <div class="contract-meta">
        <div class="left">
            <h3>CLIENT INFORMATION</h3>
            <p><strong>{{ $contract->client_name }}</strong></p>
            @if($contract->client_company)
                <p>{{ $contract->client_company }}</p>
            @endif
            @if($contract->client_email)
                <p>Email: {{ $contract->client_email }}</p>
            @endif
            @if($contract->client_phone)
                <p>Phone: {{ $contract->client_phone }}</p>
            @endif
        </div>
        <div class="right">
            <h3>CONTRACT DETAILS</h3>
            <p><strong>Start Date:</strong> {{ $contract->start_date?->format('F j, Y') ?? 'Not set' }}</p>
            <p><strong>End Date:</strong> {{ $contract->end_date?->format('F j, Y') ?? 'Not set' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($contract->status) }}</p>
            @if($contract->template)
                <p><strong>Template:</strong> {{ $contract->template->name }}</p>
            @endif
        </div>
    </div>

    <div class="content">
        <div class="content-wrapper">
            @if($processedContent)
                {!! $processedContent !!}
            @else
                <p><em>No content available for this contract.</em></p>
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
