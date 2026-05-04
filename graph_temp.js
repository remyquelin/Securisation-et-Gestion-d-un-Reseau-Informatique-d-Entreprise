// Graphique minimaliste (sparkline) avec Chart.js
function initTemperatureGraph() {
    const canvas = document.getElementById('temperature-sparkline');
    if (!canvas) return;

    // Ensure canvas fills its container
    canvas.style.width = '100%';
    canvas.style.height = '100%';

    const ctx = canvas.getContext('2d');

    // Get or create a fixed simulated temperature value stored in localStorage
    let fixed = localStorage.getItem('simTempValue');
    if (fixed === null) {
        fixed = (18 + Math.random() * 6).toFixed(1); // initial simulated value
        localStorage.setItem('simTempValue', fixed);
    }
    fixed = Number(fixed);

    const data = generateSparklineDataFixed(24, fixed);
    const labels = Array.from({ length: data.length }, () => '');

    // Destroy existing chart if present
    if (canvas._chartInstance) {
        canvas._chartInstance.destroy();
    }

    canvas._chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });

    // Update big temp value
    const valEl = document.getElementById('temp-value');
    if (valEl) {
        valEl.textContent = fixed.toFixed(1) + '°C';
    }
}

function generateSparklineDataFixed(points, fixedLast) {
    const arr = [];
    // generate values that slowly move towards fixedLast, ensuring last equals fixedLast
    let temp = fixedLast + (Math.random() - 0.5) * 4; // start near fixed
    for (let i = 0; i < points - 1; i++) {
        // small random walk biased toward fixedLast
        temp += (Math.random() - 0.5) * 1.6 + (fixedLast - temp) * 0.05;
        temp = Math.max(10, Math.min(40, temp));
        arr.push(Number(temp.toFixed(1)));
    }
    // push fixed last value
    arr.push(Number(Number(fixedLast).toFixed(1)));
    return arr;
}

// Initialiser le graphique au chargement
document.addEventListener('DOMContentLoaded', initTemperatureGraph);
