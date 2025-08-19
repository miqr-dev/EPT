<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
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
                <th>RW</th>
                <th>SN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group_scores as $i => $score)
            <tr>
                <td>U{{ $i + 1 }}</td>
                <td>{{ $score }}</td>
                <td>{{ $group_stanines[$i] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p>RW Gesamt: {{ $total_score }} | PR: {{ $prozentrang }}</p>
</body>
</html>
