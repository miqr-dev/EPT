<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .answers-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .answers-table th, .answers-table td { border: 1px solid #000; padding: 2px; text-align: center; }
        .answers-table td.selected { background: #00008b; color: #fff; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .summary-table th, .summary-table td { border: 1px solid #000; padding: 4px; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>

    <table class="answers-table">
        <thead>
            <tr>
                <th>Nr</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
            </tr>
        </thead>
        <tbody>
            @foreach($answers as $ans)
            <tr>
                <td>{{ $ans['number'] }}</td>
                @foreach(['A','B','C','D'] as $opt)
                    <td class="{{ strtoupper($ans['user_answer']) === $opt ? 'selected' : '' }}">{{ $opt }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
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

