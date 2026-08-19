<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #666; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>{{ $pageTitle }}</h1>
    <p class="meta">
        {{ $company->company_name }} · {{ $filters['start_date'] }} — {{ $filters['end_date'] }}
    </p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headings), 1) }}">@lang('messages.noRecordFound')</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
