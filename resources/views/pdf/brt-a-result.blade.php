<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $testName }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $testName }}</h1>
    <p>Datum: {{ $date }}</p>

    <h2>Ergebnisse</h2>
    <table>
        <tr>
            <th>Rohwert</th>
            <td>{{ $results['rohwert'] }}</td>
        </tr>
        <tr>
            <th>Prozentrang (PR)</th>
            <td>{{ $results['prozentrang'] }}</td>
        </tr>
        <tr>
            <th>T-Wert (Normwert)</th>
            <td>{{ $results['twert'] }}</td>
        </tr>
    </table>
</body>
</html>
