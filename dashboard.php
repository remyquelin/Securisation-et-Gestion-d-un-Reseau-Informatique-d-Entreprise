<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Caméras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <style>
        /************** Main Content ******************/
        .main-content {
            margin-left: 250px;
            padding: 2rem 3rem;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: rgb(43 43 43);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #acadae;
            border-bottom: 3px solid #dddddd;
            padding-bottom: 1px;
        }

        /**************** DASHBOARD GRID ****************/
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            grid-template-rows: auto auto auto;
            gap: 20px;
        }

        /**************** CARD DESIGN (COMMUN) ****************/
        .card {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-height: 150px;
        }

        .card h3 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #1f2933;
        }

        /**************** ÉTAT DU SYSTÈME ****************/
        .Etat-serveur {
            grid-column: 1;
            grid-row: 2;
            min-height: 140px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f2f6;
            font-size: 0.95rem;
        }

        .status-item i { width: 25px; color: #34495e; }
        .status-online { color: #2ecc71; font-weight: bold; }
        .status-offline { color: #e74c3c; font-weight: bold; }

        /**************** TEMPÉRATURE ****************/
        .Temperature {
            grid-column: 2;
            grid-row: 2;
            min-height: 250px;
            display: flex;
            flex-direction: column;
        }

        .temp-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #111827;
        }

        .temp-sparkline {
            width: 100%;
            height: 100px;
        }

        /**************** CAMERA ****************/
        .camera-card { grid-column: 1; grid-row: 1; }
        .camera-card video {
            width: 100%;
            border-radius: 10px;
            background-color: #000;
        }

        .Journal-des-evenements { grid-column: 2; grid-row: 1; }
        
        /* Style des lignes du journal */
        #event-log p {
            margin: 5px 0;
            padding: 5px 0;
            border-bottom: 1px solid #f9f9f9;
            font-size: 0.9rem;
        }
        /* Sur écrans moyens (Tablettes / Petits ordinateurs) */
        @media screen and (max-width: 1200px) {
    .main-content {
        margin-left: 0; /* On retire la marge si la sidebar devient rétractable ou masquée */
        padding: 1.5rem;
    }
    
    .dashboard-grid {
        /* On passe d'un format fixe 360px à un format plus souple */
        grid-template-columns: 1fr 1fr; 
    }
}

/* Sur petits écrans (Mobiles) */
@media screen and (max-width: 850px) {
    .dashboard-grid {
        /* Les éléments s'empilent les uns sous les autres */
        display: flex;
        flex-direction: column;
    }

    .header h1 {
        font-size: 1.8rem;
    }

    /* On force chaque carte à prendre toute la largeur sans changer ses styles internes */
    .card {
        width: 100%;
        min-height: auto;
    }

    /* On annule les positions spécifiques de la grille pour l'empilement */
    .camera-card, .Journal-des-evenements, .Etat-serveur, .Temperature {
        grid-column: auto;
        grid-row: auto;
    }
}

    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h1>Dashboard</h1>
        </header>
        <div class="dashboard-grid">
            
            <div class="Etat-serveur card">
                <h3>État du Système</h3>
                <div class="status-item">
                    <span><i class="fas fa-microchip"></i> Arduino Uno R4</span>
                    <span id="status-arduino" class="status-offline">OFFLINE</span>
                </div>
                <div class="status-item">
                    <span><i class="fas fa-database"></i> MariaDB (192.168.1.12)</span>
                    <span id="status-db" class="status-offline">OFFLINE</span>
                </div>
                <div class="status-item">
                    <span><i class="fas fa-globe"></i> Google (8.8.8.8)</span>
                    <span id="status-google" class="status-offline">OFFLINE</span>
                </div>
                <h4 style="margin-top:10px; font-size: 0.8rem; color: #7f8c8d;">
                    MAJ : <span id="last-update" style="font-weight:normal;">--:--:--</span>
                </h4>
            </div>

            <div class="Temperature card">
                <h3>Température</h3>
                <div class="temp-main">
                    <div id="temp-value" class="temp-value">-- °C</div>
                    <div id="temperature-graph" class="temp-sparkline">
                        <canvas id="temperature-sparkline"></canvas>
                    </div>
                </div>
            </div>

            <div class="Journal-des-evenements card">
                <h3>Journal des événements</h3>
                <div id="event-log">
                    <p>--</p>
                    <p>--</p>
                    <p>--</p>
                </div>
                <h3 style="margin-top:20px;">Dernier passage</h3>
                <p id="door-status">Vérification...</p>
            </div>

            <div class="camera-card card">
            <div id="video-container">
               <video id="video" controls autoplay muted></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
const video = document.getElementById('video');
const hlsUrl = 'http://192.168.1.11/hls/playlist.m3u8';

function playHLS() {
    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(hlsUrl);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = hlsUrl;
        video.addEventListener('loadedmetadata', () => video.play());
    } else {
        alert("HLS non supporté !");
    }
}

playHLS();
</script>

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
                        ticks: {
                            callback: function(value) { return value + ' °C'; }
                        }
                    }
                }
            }
        });
        

        // --- JOURNAL : afficher vrais événements (temp > 24°C et ouvertures de porte) ---
        function formatTime(ts) {
            const d = new Date(ts);
            const hours = d.getHours().toString().padStart(2, '0');
            const minutes = d.getMinutes().toString().padStart(2, '0');
            const seconds = d.getSeconds().toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }

        function renderEvents(hotTemps, doorOpens) {
            console.log('renderEvents appelé avec:', {hotTemps, doorOpens});
            
            const combined = [];
            if (hotTemps && Array.isArray(hotTemps)) {
                hotTemps.forEach(t => {
                    combined.push({
                        type: 'temp', 
                        ts: t.timestamp, 
                        text: `${parseFloat(t.value).toFixed(1)} °C`
                    });
                });
            }
            if (doorOpens && Array.isArray(doorOpens)) {
                doorOpens.forEach(d => {
                    combined.push({
                        type: 'door', 
                        ts: d.timestamp, 
                        text: ' OUVERTE'
                    });
                });
            }

            combined.sort((a, b) => new Date(b.ts) - new Date(a.ts));
            const top = combined.slice(0, 3);

            console.log('Événements combinés:', combined);
            console.log('Top 3:', top);

            if (top.length === 0) {
                document.getElementById('event-log').innerHTML = '<p style="color:#999;">Aucun événement</p>';
                return;
            }

            document.getElementById('event-log').innerHTML = top.map(e => {
                return `<p> ${e.text} à ${formatTime(e.ts)}</p>`;
            }).join('');
        }

        // --- Shared data fetch for synchronization ---
        let lastData = null;
        function fetchAndUpdateData() {
            const cachebusting = '?t=' + Date.now();
            fetch('get_data.php' + cachebusting)
                .then(res => res.json())
                .then(data => { 
                    console.log('Données reçues de get_data.php:', data);
                    lastData = data;
                    updateDashboard();
                })
                .catch((err) => {
                    console.error('Erreur fetch:', err);
                    document.getElementById('status-arduino').innerText = "OFFLINE";
                    document.getElementById('status-arduino').className = "status-offline";
                });
        }

        // --- UPDATE DASHBOARD ---
        function updateDashboard() {
            if (!lastData) return;  // Wait for data to be fetched
            const data = lastData;
            const now = new Date().toLocaleTimeString();
            if (data.temperature !== null) document.getElementById('temp-value').innerText = data.temperature + " °C";
            document.getElementById('last-update').innerText = now;
            
            const stArduino = document.getElementById('status-arduino');
            // Check if we have valid data
            if (data.temperature !== null || data.porte) {

        
                stArduino.innerText = "ONLINE";
                stArduino.className = "status-online";
            } else {
                stArduino.innerText = "OFFLINE";
                stArduino.className = "status-offline";
            }

                if (data.temperature !== null) {
                    tempChart.data.labels.push(now);
                    tempChart.data.datasets[0].data.push(data.temperature);
                    if(tempChart.data.labels.length > 15) { tempChart.data.labels.shift(); tempChart.data.datasets[0].data.shift(); }

                    // Ajuste dynamiquement l'échelle Y en fonction des données visibles
                    const vals = tempChart.data.datasets[0].data;
                    if (vals.length > 0) {
                        const minV = Math.min(...vals);
                        const maxV = Math.max(...vals);
                        const padding = 2;
                        tempChart.options.scales.y.min = Math.floor(minV - padding);
                        tempChart.options.scales.y.max = Math.ceil(maxV + padding);
                    }

                    tempChart.update('none');
                }

            // Affiche les vrais événements (temp > 24°C et ouvertures de porte)
            renderEvents(data.hot_temps || [], data.door_opens || []);

            const doorEl = document.getElementById('door-status');
            if (data.porte === "OUVERTE") {
                doorEl.innerHTML = '<span style="color:#e74c3c; font-weight:bold;"> OUVERTE</span>';
            } else if (data.porte === "FERMÉE") {
                doorEl.innerHTML = '<span style="color:#2ecc71; font-weight:bold;"> FERMÉE</span>';
            } else {
                doorEl.innerHTML = '--';
            }

            fetch('check_system.php')
                .then(res => res.json())
                .then(sys => {
                    const dbEl = document.getElementById('status-db');
                    dbEl.innerText = sys.db;
                    dbEl.className = (sys.db === "ONLINE") ? "status-online" : "status-offline";

                    const gEl = document.getElementById('status-google');
                    gEl.innerText = sys.google;
                    gEl.className = (sys.google === "ONLINE") ? "status-online" : "status-offline";
                });
        }

        // Synchronize: fetch immediately, then every 1s; update display every 2s
        fetchAndUpdateData();
        setInterval(fetchAndUpdateData, 3000);
    </script>
</body>
</html>