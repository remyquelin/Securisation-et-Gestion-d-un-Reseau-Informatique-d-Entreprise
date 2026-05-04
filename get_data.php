<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

try {
    $pdo = getPDO();
    
    // Condition robuste pour nom de capteur température (supporte accents/variantes)
    $tempCondition = "(sensor_name IN ('Temperature','Température','temp','Temp') OR sensor_name LIKE '%temp%' OR unit='C')";

    // Dernière température (supporte valeurs stockées en texte en forçant conversion numérique)
    $stmtT = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE $tempCondition ORDER BY timestamp DESC LIMIT 1");
    $t = $stmtT->fetch();

    // Dernier état de la porte
    $stmtP = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' ORDER BY timestamp DESC LIMIT 1");
    $p = $stmtP->fetch(PDO::FETCH_ASSOC);

    // Récupération de l'humidité (si elle existe)
    $stmtH = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Humidity' ORDER BY timestamp DESC LIMIT 1");
    $h = $stmtH->fetch(PDO::FETCH_ASSOC);

    // Dernières températures supérieures à 24°C (10 dernières) — conversion numérique via addition
    $stmtHot = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE $tempCondition AND (value+0) > 24 ORDER BY timestamp DESC LIMIT 10");
    $hotTemps = $stmtHot->fetchAll();
    
    // Convertir les valeurs en float
    foreach ($hotTemps as &$ht) {
        $ht['value'] = (float)$ht['value'];
    }

    // Dernières ouvertures de porte (value = 1) - 10 dernières
    $stmtDoor = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' AND value = 1 ORDER BY timestamp DESC LIMIT 10");
    $doorOpens = $stmtDoor->fetchAll();
    
    // Convertir les valeurs en int
    foreach ($doorOpens as &$d) {
        $d['value'] = (int)$d['value'];
    }

    echo json_encode([
        "temperature" => $t ? (float)$t['value'] : null,
        "temperature_ts" => $t ? $t['timestamp'] : null,
        "humidity" => $h ? (float)$h['value'] : null,
        "humidity_ts" => $h ? $h['timestamp'] : null,
        "porte"       => ($p && $p['value'] == 1) ? "OUVERTE" : "FERMÉE",
        "porte_ts"    => $p ? $p['timestamp'] : null,
        "hot_temps"   => $hotTemps,
        "door_opens"  => $doorOpens
    ]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>