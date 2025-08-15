<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Result</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
<h1>Test Result</h1>
<table>
    @foreach($result as $key => $value)
        <tr>
            <th>{{ $key }}</th>
            <td>
                @if(is_array($value))
                    {{ json_encode($value) }}
                @else
                    {{ $value }}
                @endif
            </td>
        </tr>
    @endforeach
</table>
</body>
</html>
