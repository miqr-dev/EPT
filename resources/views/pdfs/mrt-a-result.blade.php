<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { margin-bottom: 0; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>

    <div style="width:480px;height:320px;">
        <canvas id="chart" width="480" height="320"></canvas>
    </div>

    <script>
        const data = {
            labels: ['U1','U2','U3','U4','U5','U6'],
            datasets: [{
                label: 'SN',
                data: @json($results['group_stanines'] ?? []),
                borderColor: '#1d4ed8',
                backgroundColor: '#1d4ed8',
                tension: 0,
                fill: false,
                pointRadius: 6,
                pointBackgroundColor: '#1d4ed8'
            }]
        };
        const options = {
            responsive: false,
            plugins: { legend: { display: false }, title: { display: false } },
            indexAxis: 'y',
            scales: { x: { min:1, max:9, ticks:{ stepSize:1 } } }
        };
        new Chart(document.getElementById('chart'), {
            type: 'line',
            data: data,
            options: options
        });
    </script>

    <h2>Werte</h2>
    <ul>
        <li>Rohwert: {{ $results['total_score'] ?? '' }}</li>
        <li>Prozentrang: {{ $results['prozentrang'] ?? '' }}</li>
    </ul>
</body>
</html>

