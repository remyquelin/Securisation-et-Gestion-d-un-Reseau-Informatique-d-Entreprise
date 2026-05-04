<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capteurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        


       /************** MAIN CONTENT ******************/
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: clamp(1rem, 3vw, 3rem);
    display: flex;
    flex-direction: column;
}

/************** HEADER ******************/
.header {
    margin-bottom: 2rem;
}

.header h1 {
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 700;
    background: rgb(43 43 43);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    color: #acadae;
    border-bottom: 3px solid #dddddd;
    padding-bottom: 5px;
}

/**************** CARD DESIGN ****************/
.card {
    background-color: #ffffff;
    border-radius: 15px;
    padding: clamp(15px, 2vw, 25px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    min-height: 150px;
}

.card h3 {
    margin-bottom: 15px;
    font-size: clamp(1rem, 1.2vw, 1.2rem);
    color: #1f2933;
}

/**************** TEMPERATURE ****************/
.temp-main {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.temp-value {
    font-size: clamp(1.8rem, 2.5vw, 2.2rem);
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.temp-sparkline {
    width: 100%;
    height: clamp(150px, 20vw, 200px);
}

.temp-sparkline canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/************** SENSOR CARDS ******************/
.sensor-card {
    display: grid;
    grid-template-columns: 1fr;
    gap: clamp(15px, 2vw, 25px);
}

.sensor-card .card {
    width: 100%;
}

/**************** RESPONSIVE ****************/

/* Tablette */
@media (max-width: 1100px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
    }
}

/* Petit écran */
@media (max-width: 900px) {

    .sensor-card {
        grid-template-columns: 1fr;
    }
}

/* Mobile */
@media (max-width: 768px) {

    body {
        flex-direction: column;
    }

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main-content {
        margin-left: 0;
        padding: 1rem;
    }

    .temp-sparkline {
        height: 150px;
    }

    .menu-item {
        font-size: 0.85rem;
    }
}

    </style>
    <script src="graph_temp.js"></script>
</head>

<body>
    <div id="sidebar-container"></div>

<main class="content">
    <!-- contenu spécifique à la page -->
</main>

<?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h1>Les Capteurs</h1>
        </header>

        <div class="sensor-card">
            <div class="card">
                <h3>Température</h3>
                <div class="temp-main">
                    <div id="temp-value" class="temp-value">-- °C</div>
                    <div id="temp-alert" style="font-weight:700; color:#2ecc71;">Statut : OK</div>
                    <div id="temperature-graph" class="temp-sparkline"><canvas id="temperature-sparkline"></canvas></div>
                </div>
            </div>
            <div class="card">
                <h3>Humidité</h3>
                <p id="humidity-value">-- %</p>
            </div>
            <div class="card">
                <h3>Capteur d'ouverture de porte</h3>
                <p id="door-value">--</p>
            </div>
        </div>
        
    </main>

    <script>
        // --- GRAPHIQUE ---
        const ctx = document.getElementById('temperature-sparkline').getContext('2d');
        const tempChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    data: [], borderColor: '#3498db', borderWidth: 2,
                    tension: 0.4, pointRadius: 0, fill: true,
                    backgroundColor: 'rgba(52, 152, 219, 0.1)'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: {
                        display: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: { callback: function(value) { return value + ' °C'; } }
                    }
                }
            }
        });

        // --- Shared data fetch for synchronization ---
        let lastData = null;
        function fetchAndUpdateData() {
            fetch('get_data.php')
                .then(res => res.json())
                .then(data => { lastData = data; })
                .catch(() => { console.error('Erreur lors de la récupération des données'); });
        }

        // --- UPDATE SENSORS ---
        function updateSensors() {
            if (!lastData) return;
            const data = lastData;
            const now = new Date().toLocaleTimeString();
            
            // Température
            if (data.temperature !== null) {
                document.getElementById('temp-value').innerText = data.temperature + " °C";
                tempChart.data.labels.push(now);
                tempChart.data.datasets[0].data.push(data.temperature);
                if(tempChart.data.labels.length > 15) { 
                    tempChart.data.labels.shift(); 
                    tempChart.data.datasets[0].data.shift(); 
                }

                // Ajuste dynamiquement l'échelle Y
                const vals = tempChart.data.datasets[0].data;
                if (vals.length > 0) {
                    const minV = Math.min(...vals);
                    const maxV = Math.max(...vals);
                    const padding = 2;
                    tempChart.options.scales.y.min = Math.floor(minV - padding);
                    tempChart.options.scales.y.max = Math.ceil(maxV + padding);
                }

                tempChart.update('none');

                // Affiche alerte si >24°C
                const alertEl = document.getElementById('temp-alert');
                const tempEl = document.getElementById('temp-value');
                if (data.temperature > 24) {
                    alertEl.innerText = 'PROBLÈME : Température > 24 °C';
                    alertEl.style.color = '#e74c3c';
                    tempEl.style.color = '#e74c3c';
                } else {
                    alertEl.innerText = 'Statut : OK';
                    alertEl.style.color = '#2ecc71';
                    tempEl.style.color = '#111827';
                }
            }

            // Humidité
            if(data.humidity !== undefined) {
                document.getElementById('humidity-value').innerText = data.humidity + " %";
            }

            // Porte
            const doorEl = document.getElementById('door-value');
            if (data.porte === "OUVERTE") {
                doorEl.innerHTML = '<span style="color:#e74c3c; font-weight:bold;">⚠️ OUVERTE</span>';
            } else if (data.porte === "FERMÉE") {
                doorEl.innerHTML = '<span style="color:#2ecc71; font-weight:bold;">✅ FERMÉE</span>';
            } else {
                doorEl.innerHTML = '--';
            }
        }

        // Synchronize: fetch immediately, then every 1s; update display every 2s
        fetchAndUpdateData();
        setTimeout(() => {
            setInterval(fetchAndUpdateData, 1000);
            setInterval(updateSensors, 2000);
            updateSensors();
        }, 100);  // Small delay to ensure first fetch completes
    </script>
</body>

</html>
