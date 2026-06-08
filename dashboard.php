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
        /* === RESET HAUTEUR TOTALE === */
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden; /* pas de scroll global */
        }

        /************** Main Content ******************/
        .main-content {
            margin-left: 250px;
            padding: 0.6rem 1.2rem 0.6rem 1.2rem;
            height: 100vh;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            margin-bottom: 0.4rem;
            flex-shrink: 0;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: rgb(43 43 43);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #acadae;
            border-bottom: 2px solid #dddddd;
            padding-bottom: 2px;
            margin: 0;
        }

        /**************** DASHBOARD GRID ****************/
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 185px;
            gap: 10px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        /**************** CARD DESIGN (COMMUN) ****************/
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card h3 {
            margin: 0 0 8px 0;
            font-size: 0.95rem;
            color: #1f2933;
            flex-shrink: 0;
        }

        /**************** ÉTAT DU SYSTÈME ****************/
        .Etat-serveur {
            grid-column: 1;
            grid-row: 2;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #f1f2f6;
            font-size: 0.8rem;
        }

        .status-item i { width: 20px; color: #34495e; font-size: 0.75rem; }
        .status-online { color: #2ecc71; font-weight: bold; font-size: 0.78rem; }
        .status-offline { color: #e74c3c; font-weight: bold; font-size: 0.78rem; }
        .status-connecte { color: #2ecc71; font-weight: bold; font-size: 0.78rem; }
        .status-deconnecte { color: #e74c3c; font-weight: bold; font-size: 0.78rem; }

        .door-badge { font-weight: bold; font-size: 0.78rem; }
        .door-badge.open  { color: #e74c3c; }
        .door-badge.closed { color: #2ecc71; }

        /**************** TEMPÉRATURE ****************/
        .Temperature {
            grid-column: 2;
            grid-row: 2;
        }

        .temp-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            flex-shrink: 0;
        }

        .temp-sparkline {
            width: 100%;
            flex: 1;
            min-height: 0;
        }

        /**************** CAMERA ****************/
        .camera-card {
            grid-column: 1;
            grid-row: 1;
        }

        .camera-card video {
            width: 100%;
            border-radius: 8px;
            background-color: #000;
            flex: 1;
            min-height: 0;
            display: block;
        }

        /**************** JOURNAL ****************/
        .Journal-des-evenements {
            grid-column: 2;
            grid-row: 1;
        }

        .journal-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .journal-events {
            flex-shrink: 0;
        }

        #event-log p {
            margin: 0;
            padding: 4px 0;
            border-bottom: 1px solid #f4f4f4;
            font-size: 0.78rem;
            color: #2c3e50;
            line-height: 1.3;
        }

        .journal-photo {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 150px; /* garantit une hauteur visible */
            overflow: hidden;
        }

        /**************** CAROUSEL ****************/
        #carousel-container {
            background: #111;
            border-radius: 10px;
            overflow: hidden;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        #carousel-img-wrapper {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #carousel-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
            transition: opacity 0.5s ease;
        }

        #carousel-status {
            font-size: 0.78rem;
            color: #7f8c8d;
            margin: 0;
        }

        .carousel-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 8px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
        }

        #carousel-time { font-size: 0.68rem; color: #ccc; margin: 0; }
        #carousel-index { font-size: 0.65rem; color: #999; margin: 0; }

        .carousel-progress {
            height: 2px;
            background: rgba(255,255,255,0.15);
            overflow: hidden;
        }

        .carousel-progress-bar {
            height: 100%;
            background: #3498db;
            width: 0%;
            transition: width linear;
        }

        /* Responsive : tablette/mobile (conservé mais secondaire) */
        @media screen and (max-width: 1200px) {
            html, body { overflow: auto; }
            .main-content { height: auto; overflow: visible; }
            .dashboard-grid { grid-template-rows: auto auto; }
        }

        @media screen and (max-width: 768px) {
            .main-content { margin-left: 0; }
            .dashboard-grid { display: flex; flex-direction: column; }
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

            <!-- ÉTAT DU SYSTÈME -->
            <div class="Etat-serveur card">
                <h3>État du Système</h3>
                <div class="status-item">
                    <span><i class="fas fa-microchip"></i> Raspberry Pi (192.168.1.13)</span>
                    <span id="status-arduino" class="status-deconnecte">Déconnecté</span>
                </div>
                <div class="status-item">
                    <span><i class="fas fa-database"></i> MariaDB (192.168.1.12)</span>
                    <span id="status-db" class="status-deconnecte">Déconnecté</span>
                </div>
                <div class="status-item">
                    <span><i class="fas fa-globe"></i> Google (8.8.8.8)</span>
                    <span id="status-google" class="status-deconnecte">Déconnecté</span>
                </div>
                <!-- PORTE déplacée ici -->
                <div class="status-item" style="border-bottom:none; margin-bottom:0; padding-bottom:0;">
                    <span><i class="fas fa-door-open"></i> Porte de la baie</span>
                    <span id="door-status" class="door-badge">--</span>
                </div>
                <h4 style="margin-top:12px; font-size: 0.8rem; color: #7f8c8d;">
                    MAJ : <span id="last-update" style="font-weight:normal;">--:--:--</span>
                </h4>
            </div>

            <!-- TEMPÉRATURE -->
            <div class="Temperature card">
                <h3>Température</h3>
                <div class="temp-main">
                    <div id="temp-value" class="temp-value">-- °C</div>
                    <div id="temperature-graph" class="temp-sparkline">
                        <canvas id="temperature-sparkline"></canvas>
                    </div>
                </div>
            </div>

            <!-- JOURNAL DES ÉVÉNEMENTS -->
            <div class="Journal-des-evenements card">
                <h3 style="margin-bottom: 12px;">Journal des événements</h3>
                <div class="journal-content">

                    <!-- Événements (3 max) -->
                    <div class="journal-events">
                        <div id="event-log">
                            <p>--</p>
                            <p>--</p>
                            <p>--</p>
                        </div>
                    </div>

                    <!-- Carousel : 5 derniers passages -->
                    <div class="journal-photo">
                        <div id="carousel-container">
                            <div id="carousel-img-wrapper">
                                <img id="carousel-img" src="" alt="Photo du portier">
                                <p id="carousel-status">Chargement...</p>
                            </div>
                            <div class="carousel-progress">
                                <div id="carousel-progress-bar" class="carousel-progress-bar"></div>
                            </div>
                            <div class="carousel-footer">
                                <p id="carousel-time">--</p>
                                <p id="carousel-index">-- / --</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CAMÉRA LIVE -->
            <div class="camera-card card">
                <div id="video-container" style="flex:1; display:flex; flex-direction:column; min-height:0;">
                    <video id="video" controls autoplay muted style="flex:1; min-height:0;"></video>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                <script>
                    const video = document.getElementById('video');
                    let hlsUrl = 'hls/playlist.m3u8?t=' + Date.now();
                    let hls = null;
                    let retryCount = 0;
                    const MAX_RETRIES = 5;

                    function attachAndPlay() {
                        if (window.Hls && Hls.isSupported()) {
                            if (hls) { try { hls.destroy(); } catch(e){} hls = null; }
                            hls = new Hls();
                            hls.loadSource(hlsUrl);
                            hls.attachMedia(video);
                            hls.on(Hls.Events.MANIFEST_PARSED, () => { retryCount = 0; video.play().catch(()=>{}); });
                            hls.on(Hls.Events.ERROR, (event, data) => {
                                console.error('HLS error', data);
                                if (data && data.fatal) {
                                    if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                                        retryLoad();
                                    } else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                                        hls.recoverMediaError();
                                    } else {
                                        try { hls.destroy(); } catch(e){}
                                        setTimeout(retryLoad, 2000);
                                    }
                                }
                            });
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            video.src = hlsUrl;
                            video.addEventListener('loadedmetadata', () => video.play());
                            video.addEventListener('error', () => { retryLoad(); });
                        } else {
                            console.warn("HLS non supporté par ce navigateur.");
                        }

                    }

                    function retryLoad() {
                        if (retryCount >= MAX_RETRIES) { console.error('Max HLS retries reached'); return; }
                        retryCount++;
                        hlsUrl = 'hls/playlist.m3u8?t=' + Date.now();
                        const delay = Math.min(3000 * retryCount, 30000);
                        console.info(`Retrying HLS load (#${retryCount}) in ${delay}ms`);
                        setTimeout(() => { attachAndPlay(); }, delay);
                    }

                    // Lancer la lecture
                    attachAndPlay();
                </script>
            </div>

        </div>
    </main>

    <script>
        // --- GRAPHIQUE TEMPÉRATURE ---
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
                        ticks: { callback: v => v + ' °C' }
                    }
                }
            }
        });

        // --- CACHE DES DERNIÈRES DONNÉES ---
        let cachedData = {
            temperature: null,
            porte: null,
            journal: {
                alertes_temp: [],
                alertes_hum: [],
                alertes_porte: []
            }
        };

        // --- FORMAT HEURE ---
        function formatTime(ts) {
            const d = new Date(ts);
            return d.getHours().toString().padStart(2,'0') + ':' +
                   d.getMinutes().toString().padStart(2,'0') + ':' +
                   d.getSeconds().toString().padStart(2,'0');
        }

        // --- JOURNAL ÉVÉNEMENTS (max 5) ---
        function renderEvents(journal) {
            const combined = [];

            // Alertes Température
            if (journal.alertes_temp && Array.isArray(journal.alertes_temp)) {
                journal.alertes_temp.forEach(t => {
                    combined.push({ ts: t.timestamp, text: `Température de ${parseFloat(t.value).toFixed(1)}°C` });
                });
            }

            // Alertes Humidité
            if (journal.alertes_hum && Array.isArray(journal.alertes_hum)) {
                journal.alertes_hum.forEach(h => {
                    combined.push({ ts: h.timestamp, text: `Humidité de ${parseFloat(h.value).toFixed(1)}%` });
                });
            }

            // Ouvertures Porte
            if (journal.alertes_porte && Array.isArray(journal.alertes_porte)) {
                journal.alertes_porte.forEach(d => {
                    combined.push({ ts: d.timestamp, text: 'Porte de la baie OUVERTE' });
                });
            }

            // Événements explicites du journal
            if (journal.event_journal && Array.isArray(journal.event_journal)) {
                journal.event_journal.forEach(d => {
                    combined.push({ ts: d.timestamp, text: d.event_description });
                });
            }

            // Déduplication: clé basée sur texte + ts arrondi à la seconde
            const unique = new Map();
            combined.forEach(e => {
                const tsKey = e.ts ? Math.round(new Date(e.ts).getTime() / 1000) : 0;
                const key = `${e.text}|${tsKey}`;
                if (!unique.has(key)) unique.set(key, e);
            });

            const deduped = Array.from(unique.values());
            deduped.sort((a, b) => new Date(b.ts) - new Date(a.ts));
            const top = deduped.slice(0, 5);

            const logEl = document.getElementById('event-log');
            logEl.innerHTML = top.length ? top.map(e => `<p>${e.text} à ${formatTime(e.ts)}</p>`).join('') : '<p>Aucun incident</p>';
        }

        // --- FETCH DONNÉES ---
        let lastData = null;
        function fetchAndUpdateData() {
            fetch('get_data.php?t=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    lastData = { ...data, journal: cachedData.journal };
                    if (lastData.porte === null && cachedData.porte !== null) {
                        lastData.porte = cachedData.porte;
                    }
                    // Mémoriser les données valides pour les réutiliser en cas de déconnexion
                    if (data.temperature !== null) cachedData.temperature = data.temperature;
                    if (data.porte !== null) cachedData.porte = data.porte;
                    updateDashboard();

                    return fetch('get_alerts.php?t=' + Date.now())
                        .then(res => res.json())
                        .then(alerts => {
                            if (alerts && alerts.journal) {
                                lastData = { ...lastData, journal: alerts.journal };
                                cachedData.journal = alerts.journal;
                                updateDashboard();
                            }
                        })
                        .catch(() => {
                            // Si le journal est indisponible, on conserve l'ancien journal en cache.
                        });
                })
                .catch(() => {
                    document.getElementById('status-arduino').innerText = "Déconnecté";
                    document.getElementById('status-arduino').className = "status-deconnecte";
                    // Garder les dernières données en cache
                    updateDashboard();
                });
        }

        function updateDashboard() {
            // Utiliser les données en cache si pas de données fraîches
            const data = lastData || cachedData;
            if (!data || (data.temperature === null && data.porte === null)) return;
            
            const now = new Date().toLocaleTimeString();

            if (data.temperature !== null) document.getElementById('temp-value').innerText = data.temperature + " °C";
            document.getElementById('last-update').innerText = now;

            if (data.temperature !== null) {
                tempChart.data.labels.push(now);
                tempChart.data.datasets[0].data.push(data.temperature);
                if (tempChart.data.labels.length > 15) {
                    tempChart.data.labels.shift();
                    tempChart.data.datasets[0].data.shift();
                }
                const vals = tempChart.data.datasets[0].data;
                if (vals.length > 0) {
                    const minV = Math.min(...vals);
                    const maxV = Math.max(...vals);
                    tempChart.options.scales.y.min = Math.floor(minV - 2);
                    tempChart.options.scales.y.max = Math.ceil(maxV + 2);
                }
                tempChart.update('none');
            }

            renderEvents(data.journal || {});

            // ÉTAT DE LA PORTE dans état du système (garder le dernier état)
            const doorEl = document.getElementById('door-status');
            const doorState = (data.porte !== null && data.porte !== undefined) ? data.porte : cachedData.porte;
            if (doorState === "OUVERTE") {
                doorEl.innerHTML = ' OUVERTE';
                doorEl.className = 'door-badge open';
            } else if (doorState === "FERMÉE") {
                doorEl.innerHTML = ' FERMÉE';
                doorEl.className = 'door-badge closed';
            } else {
                doorEl.innerHTML = ' INCONNU';
                doorEl.className = 'door-badge';
            }

            fetch('check_system.php')
                .then(res => res.json())
                .then(sys => {
                    const dbEl = document.getElementById('status-db');
                    dbEl.innerText = (sys.db === "ONLINE") ? "Connecté" : "Déconnecté";
                    dbEl.className = (sys.db === "ONLINE") ? "status-connecte" : "status-deconnecte";

                    const gEl = document.getElementById('status-google');
                    gEl.innerText = (sys.google === "ONLINE") ? "Connecté" : "Déconnecté";
                    gEl.className = (sys.google === "ONLINE") ? "status-connecte" : "status-deconnecte";

                    const arduinoEl = document.getElementById('status-arduino');
                    arduinoEl.innerText = (sys.arduino === "ONLINE") ? "Connecté" : "Déconnecté";
                    arduinoEl.className = (sys.arduino === "ONLINE") ? "status-connecte" : "status-deconnecte";
                });
        }

        // --- CAROUSEL AUTO — 5 derniers passages, toutes les 5 secondes ---
        let photosList = [];
        let currentPhotoIndex = 0;
        let autoPlayInterval = null;
        const SLIDE_DELAY = 5000; // ← 5 secondes
        const MAX_PHOTOS = 5;     // ← uniquement les 5 derniers passages

        function loadPhotos() {
            fetch('list_photos.php?t=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    // Trier par date décroissante et garder seulement les 5 derniers
                    const all = data.photos || [];
                    all.sort((a, b) => b.time - a.time);
                    photosList = all.slice(0, MAX_PHOTOS);

                    if (photosList.length > 0) {
                        displayPhoto(currentPhotoIndex);
                    } else {
                        showNoPhotos();
                    }
                })
                .catch(() => showNoPhotos());
        }

        function displayPhoto(index) {
            if (photosList.length === 0) { showNoPhotos(); return; }
            currentPhotoIndex = ((index % photosList.length) + photosList.length) % photosList.length;
            const photo = photosList[currentPhotoIndex];
            const img = document.getElementById('carousel-img');
            const status = document.getElementById('carousel-status');

            img.style.opacity = '0';
            setTimeout(() => {
                img.src = photo.url;
                img.style.display = 'block';
                status.style.display = 'none';
                img.style.transition = 'opacity 0.5s ease';
                img.style.opacity = '1';
            }, 200);

            document.getElementById('carousel-time').innerText = new Date(photo.time * 1000).toLocaleString();
            document.getElementById('carousel-index').innerText = (currentPhotoIndex + 1) + ' / ' + photosList.length;

            // Progress bar
            const bar = document.getElementById('carousel-progress-bar');
            bar.style.transition = 'none';
            bar.style.width = '0%';
            bar.offsetWidth; // force reflow
            bar.style.transition = `width ${SLIDE_DELAY}ms linear`;
            bar.style.width = '100%';
        }

        function showNoPhotos() {
            document.getElementById('carousel-img').style.display = 'none';
            const status = document.getElementById('carousel-status');
            status.style.display = 'block';
            status.innerText = 'Aucune photo';
            document.getElementById('carousel-time').innerText = '--';
            document.getElementById('carousel-index').innerText = '0 / 0';
        }

        function startAutoPlay() {
            if (autoPlayInterval) clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(() => {
                displayPhoto(currentPhotoIndex + 1);
            }, SLIDE_DELAY);
        }

        // --- INIT ---
        fetchAndUpdateData();
        loadPhotos();
        setTimeout(() => startAutoPlay(), 1500);

        setInterval(fetchAndUpdateData, 3000);
        setInterval(loadPhotos, 10000);
    </script>
</body>
</html>