<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

/**************** FILTRES & RECHERCHE ****************/
.filter-bar {
    display: flex;
    gap: clamp(8px, 1vw, 12px);
    margin-bottom: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-bar input,
.filter-bar select {
    padding: clamp(8px, 1vw, 10px) clamp(10px, 1.5vw, 14px);
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: clamp(0.85rem, 1vw, 0.95rem);
}

.filter-bar input {
    flex: 1;
    min-width: 180px;
}

.filter-bar select {
    min-width: 140px;
}

.filter-bar button {
    padding: clamp(8px, 1vw, 10px) clamp(12px, 1.5vw, 16px);
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: clamp(0.85rem, 1vw, 0.95rem);
    transition: background-color 0.3s ease;
}

.filter-bar button:hover {
    background-color: #2563eb;
}

.sort-button {
    padding: clamp(6px, 1vw, 8px) clamp(10px, 1.2vw, 12px);
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    font-size: clamp(0.8rem, 0.9vw, 0.9rem);
    transition: all 0.3s ease;
}

.sort-button:hover,
.sort-button.active {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.results-info {
    font-size: clamp(0.8rem, 0.9vw, 0.9rem);
    color: #6b7280;
    margin-top: 12px;
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

    .filter-bar {
        gap: 8px;
    }

    .filter-bar input {
        flex: 100%;
    }

    .filter-bar select,
    .filter-bar button,
    .sort-button {
        flex: 1;
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

    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-bar input,
    .filter-bar select,
    .filter-bar button,
    .sort-button {
        width: 100%;
    }

    .menu-item {
        font-size: 0.85rem;
    }
}

    </style>
</head>
<body>
    <div id="sidebar-container"></div>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h1>Journal d'événement</h1>
        </header>
        <div class="card">
            <h3>Historique des alertes</h3>

            <!-- Barre de filtrage -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="Rechercher..." style="flex: 0 0 400px; min-width: auto;">
                <select id="filterType">
                    <option value="">Tous les types</option>
                    <option value="Temperature">Temperature</option>
                    <option value="Porte_Baie">Porte</option>
                </select>
                <button class="sort-button active" id="dateButton" onclick="toggleDateSort(this)" style="margin-left: auto;">Ancienne date</button>
                <button onclick="resetFilters()">Reinitialiser</button>
            </div>

            <?php
            require_once __DIR__ . '/db_config.php';

            $alerts = [];
            $dbConnected = false;

            try {
                $pdo = getPDO();
                $dbConnected = true;
                
                // Récupération des données des capteurs
                $stmt = $pdo->query("SELECT id, timestamp as event_time, sensor_name as event_type, value as message FROM sensors_data ORDER BY timestamp DESC LIMIT 500");
                $sensorAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Traitement des données pour les formater correctement
                foreach ($sensorAlerts as $key => $alert) {
                    $sensorAlerts[$key]['severity'] = 'info';
                    
                    // Déterminer la sévérité basée sur le type et la valeur
                    if ($alert['event_type'] === 'Temperature' && $alert['message'] > 28) {
                        $sensorAlerts[$key]['severity'] = 'warning';
                    } elseif ($alert['event_type'] === 'Temperature' && $alert['message'] > 30) {
                        $sensorAlerts[$key]['severity'] = 'danger';
                    }
                }
                
                $alerts = $sensorAlerts;
                
                // Récupération des événements explicitement enregistrés
                try {
                    $stmtEvents = $pdo->query("SELECT id, event_time, event_type, message, severity FROM events ORDER BY event_time DESC LIMIT 500");
                    $eventAlerts = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
                    $alerts = array_merge($eventAlerts, $alerts);
                    usort($alerts, function($a, $b) {
                        $timeA = strtotime($a['event_time']);
                        $timeB = strtotime($b['event_time']);
                        return $timeB - $timeA;
                    });
                    $alerts = array_slice($alerts, 0, 500); // Limiter à 500
                } catch (Exception $e) {
                    // Si la table events n'existe pas, continuer avec les données des capteurs
                }
            } catch (Exception $e) {
                $dbConnected = false;
            }
            ?>

            <!-- Table d'alertes -->
            <div style="overflow:auto; max-height:550px; margin-top:10px; border-radius: 8px;">
                <table id="eventsTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid #eee; background-color:#f9fafb; position: sticky; top: 0;">
                            <th style="padding:8px; width:18%">Date</th>
                            <th style="padding:8px; width:25%">Type</th>
                            <th style="padding:8px; width:57%">Message</th>
                        </tr>
                    </thead>
                    <tbody id="eventsBody">
                    <?php foreach ($alerts as $a): ?>
                        <?php
                            $displayMessage = htmlspecialchars($a['message']);
                            $type = htmlspecialchars($a['event_type']);
                            
                            // Formatage des messages selon le type
                            if ($type === 'Temperature') {
                                // Format: value est la température
                                $displayMessage = number_format((float)$a['message'], 1, '.', '') . ' °C';
                            } elseif ($type === 'Porte_Baie' || $type === 'Porte') {
                                // Format: 0 = fermée, 1 = ouverte
                                $displayMessage = ($a['message'] == 1 || strtoupper($a['message']) === 'OUVERTE') ? ' OUVERTE' : ' FERMÉE';
                            }
                            
                            $severity = isset($a['severity']) ? htmlspecialchars($a['severity']) : 'info';
                        ?>
                        <tr class="event-row" data-type="<?= $type ?>" data-message="<?= htmlspecialchars($a['message']) ?>" style="border-bottom:1px solid #f4f4f4; background-color: <?php 
                            echo ($severity === 'danger') ? '#fee2e2' : (($severity === 'warning') ? '#fef3c7' : '#f3f4f6'); 
                        ?>">
                            <td style="padding:8px 10px; font-size:0.9rem; color:#374151; white-space:nowrap;"><?= htmlspecialchars($a['event_time']) ?></td>
                            <td style="padding:8px 10px; font-weight:600; color:#111827; white-space:nowrap;"><?= $type ?></td>
                            <td style="padding:8px 10px; color:#4b5563; font-weight: <?php echo ($severity !== 'info') ? '600' : '400'; ?>;"><?= $displayMessage ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="results-info">
                <span id="resultsCount" style="font-weight: 600;"><?= count($alerts) ?> evenement(s)</span> | Source : <?php echo $dbConnected ? 'base de donnees' : 'donnees simulees'; ?>
            </div>

            <script>
                let allRows = [];
                let currentSort = 'date-asc';

                function initTable() {
                    const tbody = document.getElementById('eventsBody');
                    allRows = Array.from(tbody.querySelectorAll('tr.event-row'));
                }

                function applyFiltersAndSort() {
                    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                    const filterType = document.getElementById('filterType').value.toLowerCase();

                    let filtered = allRows.filter(row => {
                        const type = row.dataset.type.toLowerCase();
                        const message = row.dataset.message.toLowerCase();
                        
                        const matchesSearch = !searchTerm || type.includes(searchTerm) || message.includes(searchTerm);
                        const matchesType = !filterType || type.includes(filterType);
                        
                        return matchesSearch && matchesType;
                    });

                    filtered.sort((a, b) => {
                        const getDate = (row) => new Date(row.cells[0].textContent).getTime();

                        if (currentSort === 'date-asc') {
                            return getDate(a) - getDate(b);
                        } else if (currentSort === 'date-desc') {
                            return getDate(b) - getDate(a);
                        }
                        return 0;
                    });

                    const tbody = document.getElementById('eventsBody');
                    tbody.innerHTML = '';
                    filtered.forEach(row => {
                        tbody.appendChild(row.cloneNode(true));
                    });

                    document.getElementById('resultsCount').textContent = filtered.length + ' evenement(s)';
                }

                function toggleDateSort(btn) {
                    if (currentSort === 'date-asc') {
                        currentSort = 'date-desc';
                        btn.textContent = 'Date récent';
                    } else {
                        currentSort = 'date-asc';
                        btn.textContent = 'Ancienne date';
                    }
                    applyFiltersAndSort();
                }

                function resetFilters() {
                    document.getElementById('searchInput').value = '';
                    document.getElementById('filterType').value = '';
                    applyFiltersAndSort();
                }

                document.getElementById('searchInput').addEventListener('keyup', applyFiltersAndSort);
                document.getElementById('filterType').addEventListener('change', applyFiltersAndSort);

                initTable();
            </script>
        </div>
    </main>
</body>
</html>
