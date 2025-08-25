<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 4px; }
        .result-table { width: 100%; border-collapse: collapse; }
        .result-table th, .result-table td { border: 1px solid #000; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $test_name }} Ergebnis</h1>
    <table class="meta-table">
        <tr>
            <td><strong>Name:</strong></td><td>{{ $participant_name }}</td>
            <td><strong>Geburtsdatum:</strong></td><td>{{ $birth_date }}</td>
        </tr>
        <tr>
            <td><strong>Alter:</strong></td><td>{{ $age }}</td>
            <td><strong>Datum:</strong></td><td>{{ $exam_date }}</td>
        </tr>
        <tr>
            <td><strong>Dauer:</strong></td><td>{{ $duration }}</td>
            <td></td><td></td>
        </tr>
    </table>
    <table class="result-table">
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
