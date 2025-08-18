<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <table>
        <thead>
            <tr>
                <th>Rohwert</th>
                <th>PR</th>
                <th>T-Wert</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $rohwert }}</td>
                <td>{{ $prozentrang }}</td>
                <td>{{ $twert }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
