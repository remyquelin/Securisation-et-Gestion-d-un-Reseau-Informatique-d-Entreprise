<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

function normalizeDoorState($rawValue) {
    if ($rawValue === null) {
        return null;
    }
    $value = trim((string)$rawValue);
    if ($value === '') {
        return null;
    }
    // 1 = OUVERTE, 0 = FERMÉE
    if ($value === '1') {
        return 'OUVERTE';
    }
    if ($value === '0') {
        return 'FERMÉE';
    }
    $upper = mb_strtoupper($value, 'UTF-8');
    if (in_array($upper, ['FERMÉE', 'FERMEE', 'FERME', 'CLOSED', 'FERMEE'], true)) {
        return 'FERMÉE';
    }
    if (in_array($upper, ['OUVERTE', 'OPEN'], true)) {
        return 'OUVERTE';
    }
    return null;
}

try {
    $pdo = getPDO();
    if (!$pdo) throw new Exception("Erreur de connexion BDD");

    // 1. Dernière température
    $stmtT = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name IN ('Temperature', 'Température') ORDER BY timestamp DESC LIMIT 1");
    $t = $stmtT->fetch();

    // 2. Dernier état de la porte
    $stmtP = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' AND value IS NOT NULL ORDER BY timestamp DESC LIMIT 1");
    $p = $stmtP->fetch();

    // 3. Dernière humidité
    $stmtH = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Humidite' ORDER BY timestamp DESC LIMIT 1");
    $h = $stmtH->fetch();

    // 4. Alertes Température > 24°C (Optimisé avec CAST pour le sérieux du dossier technique)
    $stmtHot = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name IN ('Temperature', 'Température') AND CAST(value AS DECIMAL(10,2)) > 24.00 ORDER BY timestamp DESC LIMIT 10");
    $hotTemps = $stmtHot->fetchAll();

    // 5. Alertes Ouvertures Porte
    $stmtDoor = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' AND value = 1 ORDER BY timestamp DESC LIMIT 10");
    $doorOpens = $stmtDoor->fetchAll();

    // Formatage de la réponse
    echo json_encode([
        "temperature"    => $t ? (float)$t['value'] : null,
        "temperature_ts" => $t ? $t['timestamp'] : null,
        "humidity"       => $h ? (float)$h['value'] : null,
        "porte"          => normalizeDoorState($p['value'] ?? null),
        "hot_temps"      => $hotTemps,
        "door_opens"     => $doorOpens
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}