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

    $stmtT = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name IN ('Temperature', 'Température') ORDER BY timestamp DESC LIMIT 1");
    $t = $stmtT->fetch();

    $stmtP = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' AND value IS NOT NULL ORDER BY timestamp DESC LIMIT 1");
    $p = $stmtP->fetch();

    $stmtH = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Humidite' ORDER BY timestamp DESC LIMIT 1");
    $h = $stmtH->fetch();

    $stmtTempLog = $pdo->query("SELECT value, timestamp FROM sensors_data 
        WHERE sensor_name IN ('Temperature', 'Température') 
        AND (CAST(value AS DECIMAL(10,2)) > 24.00 OR CAST(value AS DECIMAL(10,2)) < 18.00) 
        ORDER BY timestamp DESC LIMIT 10");
    $tempLog = $stmtTempLog->fetchAll();

    $stmtHumLog = $pdo->query("SELECT value, timestamp FROM sensors_data 
        WHERE sensor_name='Humidite' 
        AND (CAST(value AS DECIMAL(10,2)) < 35.00 OR CAST(value AS DECIMAL(10,2)) > 65.00) 
        ORDER BY timestamp DESC LIMIT 10");
    $humLog = $stmtHumLog->fetchAll();

    $stmtDoorLog = $pdo->query("SELECT value, timestamp FROM sensors_data WHERE sensor_name='Porte_Baie' AND value = 1 ORDER BY timestamp DESC LIMIT 10");
    $doorLog = $stmtDoorLog->fetchAll();

    $stmtEventJournal = $pdo->query("SELECT event_description, severity, timestamp FROM event_journal ORDER BY timestamp DESC LIMIT 10");
    $eventJournal = $stmtEventJournal->fetchAll();

    echo json_encode([
        "temperature"    => $t ? (float)$t['value'] : null,
        "temperature_ts" => $t ? $t['timestamp'] : null,
        "humidity"       => $h ? (float)$h['value'] : null,
        "porte"          => normalizeDoorState($p['value'] ?? null),
        "journal" => [
            "alertes_temp" => $tempLog,
            "alertes_hum"  => $humLog,
            "alertes_porte" => $doorLog,
            "event_journal" => $eventJournal
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}