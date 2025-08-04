<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template->name }}</title>
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

        .header .description {
            margin: 10px 0 0 0;
            font-style: italic;
            color: #666;
        }

        /* Content area */
        .content {
            margin: 20px 0;
        }

        /* Table styles */
        table {
            border-collapse: collapse;
            margin: 15px 0;
            width: 100%;
            border: 2px solid #333;
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

        /* List styles */
        ul {
            list-style-type: disc;
            margin-left: 20px;
            margin: 15px 0;
        }

        ol {
            list-style-type: decimal;
            margin-left: 20px;
            margin: 15px 0;
        }

        li {
            margin: 5px 0;
            padding-left: 5px;
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

        blockquote {
            border-left: 4px solid #ccc;
            padding-left: 15px;
            margin: 15px 0;
            font-style: italic;
            color: #666;
        }

        hr {
            border: none;
            border-top: 2px solid #ccc;
            margin: 25px 0;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $template->name }}</h1>
        @if($template->description)
            <p class="description">{{ $template->description }}</p>
        @endif
    </div>

    <div class="content">
        @if($template->content)
            {!! $template->content !!}
        @else
            <p style="text-align: center; font-style: italic; color: #666; margin: 50px 0;">
                No template content available
            </p>
        @endif
    </div>

    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} | Template: {{ $template->name }}
        @if($company)
            | {{ $company->name }}
        @endif
    </div>
</body>
</html>
