<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        .info-table { width: 60%; margin-bottom: 20px; }
        .chart { margin-top: 20px; }
        .progress { width:400px;height:20px;border:1px solid #000;margin-top:20px; }
        .progress-bar { height:100%; background-color:#dc2626; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>

    <table class="info-table">
        <tr>
            <th>Rohwert</th>
            <td>{{ $total_score }} von 60</td>
        </tr>
        <tr>
            <th>Benötigte Zeit</th>
            <td>
                @if(!is_null($total_time_seconds))
                    {{ sprintf('%02d:%02d', intdiv($total_time_seconds,60), $total_time_seconds%60) }} Minuten
                @else
                    –
                @endif
            </td>
        </tr>
        <tr>
            <th>Prozentrang</th>
            <td>{{ $prozentrang }}</td>
        </tr>
    </table>

    @if($chart_image)
    <div class="chart">
        <img src="data:image/png;base64,{{ $chart_image }}" width="480" height="320" alt="Chart">
    </div>
    @endif

    <div class="progress">
        <div class="progress-bar" style="width: {{ $prozentrang }}%"></div>
    </div>

    <h3>Antworten</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Antwort</th>
                <th>Richtig</th>
                <th>Zeit (mm:ss)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($answers as $a)
            <tr>
                <td>{{ $a['number'] }}</td>
                <td>{{ $a['user_answer'] ?? '–' }}</td>
                <td>{{ $a['correct'] }}</td>
                <td>{{ $a['time_formatted'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
