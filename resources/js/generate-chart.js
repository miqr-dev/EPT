const { ChartJSNodeCanvas } = require('chartjs-node-canvas');
const chartjsPluginAnnotation = require('chartjs-plugin-annotation');

const width = 480;
const height = 320;

const chartCallback = (ChartJS) => {
    ChartJS.defaults.responsive = true;
    ChartJS.defaults.maintainAspectRatio = false;
};

const chartJSNodeCanvas = new ChartJSNodeCanvas({ width, height, chartCallback, plugins: { modern: [chartjsPluginAnnotation] } });

(async () => {
    const data = JSON.parse(process.argv[2]);

    const configuration = {
        type: 'line',
        data: {
            labels: ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'],
            datasets: [
                {
                    label: 'SN',
                    data: data,
                    borderColor: '#1d4ed8',
                    backgroundColor: '#1d4ed8',
                    tension: 0,
                    pointRadius: 6,
                    pointBackgroundColor: '#1d4ed8',
                    fill: false
                }
            ]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    min: 1,
                    max: 9,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                annotation: {
                    annotations: {
                        rangeBox: {
                            type: 'box',
                            yMin: 4,
                            yMax: 6,
                            backgroundColor: 'rgba(144,238,144,0.3)',
                            borderWidth: 0
                        }
                    }
                }
            }
        }
    };

    const image = await chartJSNodeCanvas.renderToDataURL(configuration);
    console.log(image);
})();
