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
                @foreach (array_keys($group_scores) as $key)
                    <th>{{ $key }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Rohwert</td>
                @foreach ($group_scores as $score)
                    <td>{{ $score }}</td>
                @endforeach
            </tr>
            <tr>
                <td>T-Wert</td>
                @foreach ($group_t_values as $t_value)
                    <td>{{ $t_value }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <h2>Antworten</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Antwort</th>
                <th>Gruppe</th>
                <th>Punkte</th>
                <th>Zeit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($answers as $answer)
                <tr>
                    <td>{{ $answer['number'] }}</td>
                    <td>{{ $answer['selected_category'] ?? '–' }}</td>
                    <td>{{ $answer['selected_group'] ?? '–' }}</td>
                    <td>{{ $answer['points'] ?? '–' }}</td>
                    <td>{{ gmdate('i:s', $answer['time_seconds']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
