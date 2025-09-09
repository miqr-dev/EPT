<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .correct { background-color: #c8e6c9; }
        .incorrect { background-color: #ffcdd2; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>

    <h2>Gesamtergebnis</h2>
    <table>
        <thead>
            <tr>
                <th>Gesamtpunktzahl</th>
                <th>Prozentrang</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $total_score }}</td>
                <td>{{ $prozentrang }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Gruppenergebnisse</h2>
    <table>
        <thead>
            <tr>
                <th>Gruppe</th>
                <th>Punktzahl</th>
                <th>Stanine</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group_scores as $index => $score)
            <tr>
                <td>Gruppe {{ $index + 1 }}</td>
                <td>{{ $score }}</td>
                <td>{{ $group_stanines[$index] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($chart_image)
    <div style="text-align: center; margin-top: 20px;">
        <img src="{{ $chart_image }}" alt="Chart" style="width: 100%; max-width: 480px;"/>
    </div>
    @endif

    <h2>Detailergebnisse</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ihre Antwort</th>
                <th>Richtige Antwort</th>
                <th>Zeit (s)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($answers as $answer)
            <tr class="{{ $answer['is_correct'] ? 'correct' : 'incorrect' }}">
                <td>{{ $answer['number'] }}</td>
                <td>{{ $answer['user_answer'] }}</td>
                <td>{{ implode(', ', $answer['correct_answers']) }}</td>
                <td>{{ $answer['time_seconds'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
