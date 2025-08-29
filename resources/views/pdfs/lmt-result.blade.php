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
                <th></th>
                <th>L1</th>
                <th>L2</th>
                <th>F-</th>
                <th>F+</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Rohwert</td>
                <td>{{ $group_scores['L1'] ?? '–' }}</td>
                <td>{{ $group_scores['L2'] ?? '–' }}</td>
                <td>{{ $group_scores['F-'] ?? '–' }}</td>
                <td>{{ $group_scores['F+'] ?? '–' }}</td>
            </tr>
            <tr>
                <td>T-Wert</td>
                <td>{{ $group_t_values['L1'] ?? '–' }}</td>
                <td>{{ $group_t_values['L2'] ?? '–' }}</td>
                <td>{{ $group_t_values['F-'] ?? '–' }}</td>
                <td>{{ $group_t_values['F+'] ?? '–' }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
