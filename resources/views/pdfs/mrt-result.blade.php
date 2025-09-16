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
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>
    <table>
        <thead>
            <tr>
                <th>Gruppe</th>
                <th>Rohwert</th>
                <th>Stanine</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group_scores as $idx => $score)
            <tr>
                <td>{{ 'U' . ($idx + 1) }}</td>
                <td>{{ $score }}</td>
                <td>{{ $group_stanines[$idx] ?? '' }}</td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Gesamt</strong></td>
                <td>{{ $total_score }}</td>
                <td>PR: {{ $prozentrang }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
