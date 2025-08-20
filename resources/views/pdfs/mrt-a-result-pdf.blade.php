<!DOCTYPE html>
<html>
<head>
    <title>MRT-A Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; }
        .chart-container { width: 480px; height: 320px; margin: auto; }
        .table { width: 100%; max-width: 400px; margin: 20px auto; border-collapse: collapse; }
        .table, .table th, .table td { border: 1px solid #ddd; }
        .table th, .table td { padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center;">MRT-A Results</h1>
        <div class="chart-container">
            <canvas id="mrtChart"></canvas>
        </div>
        <table class="table">
            <tr>
                <th>Rohwert</th>
                <td>{{ $results['total_score'] }} von 60</td>
            </tr>
            <tr>
                <th>Prozentrang</th>
                <td>{{ $results['prozentrang'] }}%</td>
            </tr>
        </table>
    </div>
    <script>
        const chartData = {
            labels: ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'],
            datasets: [{
                label: 'SN',
                data: {{ json_encode($results['group_stanines']) }},
                borderColor: '#1d4ed8',
                backgroundColor: '#1d4ed8',
                tension: 0,
                pointRadius: 6,
                pointBackgroundColor: '#1d4ed8',
                fill: false
            }]
        };

        const chartOptions = {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            animation: {
                onComplete: () => {
                    document.body.classList.add('chart-rendered');
                }
            },
            indexAxis: 'y',
            scales: {
                x: {
                    min: 1,
                    max: 9,
                    ticks: { stepSize: 1 }
                }
            }
        };

        new Chart(document.getElementById('mrtChart'), {
            type: 'line',
            data: chartData,
            options: chartOptions
        });
    </script>
</body>
</html>
