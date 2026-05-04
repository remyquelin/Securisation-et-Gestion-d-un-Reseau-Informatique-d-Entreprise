<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

try {
    $pdo = getPDO();

    /* ============================= */
    /* TEMPÉRATURE > 24°C (3 dernières) */
    /* ============================= */

    $stmtHot = $pdo->query("
        SELECT value, timestamp
        FROM sensors_data
        WHERE sensor_name = 'Temperature'
        AND value > 24
        ORDER BY timestamp DESC
        LIMIT 3
    ");

    $hotTempsRaw = $stmtHot->fetchAll(PDO::FETCH_ASSOC);

    $hotTemps = array_map(function($row) {
        return [
            "value" => (float)$row['value'],
            "timestamp" => $row['timestamp']
        ];
    }, $hotTempsRaw);


    /* ============================= */
    /* PORTE OUVERTE (value = 1) */
    /* ============================= */

    $stmtDoor = $pdo->query("
        SELECT value, timestamp
        FROM sensors_data
        WHERE sensor_name = 'Porte_Baie'
        AND value = 1
        ORDER BY timestamp DESC
        LIMIT 3
    ");

    $doorRaw = $stmtDoor->fetchAll(PDO::FETCH_ASSOC);

    $doorOpens = array_map(function($row) {
        return [
            "status" => "OUVERTE",
            "timestamp" => $row['timestamp']
        ];
    }, $doorRaw);


    /* ============================= */
    /* RÉPONSE JSON */
    /* ============================= */

    echo json_encode([
        "hot_temps"  => $hotTemps,
        "door_opens" => $doorOpens
    ]);

} catch (Exception $e) {

    echo json_encode([
        "error" => "Erreur base de données",
        "details" => $e->getMessage()
    ]);
}
?>
