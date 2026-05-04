<?php
header('Content-Type: application/json');

$response = ["db" => "OFFLINE", "google" => "OFFLINE", "arduino" => "OFFLINE"];
$conn = null;

// 1. Test MariaDB (192.168.1.12)
require_once __DIR__ . '/db_config.php';
try {
    $conn = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 1,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $response["db"] = "ONLINE";
    
    // Vérifier si Arduino a envoyé des données récemment (moins de 60 secondes)
    try {
        $stmt = $conn->query("SELECT MAX(timestamp) as last_update FROM sensors_data");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $arduinoStatus = "OFFLINE";
        if ($result && $result['last_update']) {
            $lastUpdate = strtotime($result['last_update']);
            $now = time();
            if (($now - $lastUpdate) < 60) {
                $arduinoStatus = "ONLINE";
            }
        }
        
        // Vérifier le dernier statut enregistré
        $stmtStatus = $conn->query("SELECT arduino_status FROM arduino_status_log ORDER BY check_time DESC LIMIT 1");
        $lastStatusRecord = $stmtStatus->fetch(PDO::FETCH_ASSOC);
        $lastStatus = $lastStatusRecord ? $lastStatusRecord['arduino_status'] : null;
        
        // Si changement d'état, enregistrer une alerte
        if ($lastStatus !== $arduinoStatus) {
            try {
                $alertMessage = $arduinoStatus === "ONLINE" ? "Arduino connecté" : "Arduino déconnecté";
                $conn->exec("INSERT INTO arduino_status_log (arduino_status, alert_message, check_time) 
                           VALUES ('$arduinoStatus', '$alertMessage', NOW())");
                
                // Enregistrer dans la table events si elle existe
                try {
                    $conn->exec("INSERT INTO events (event_time, event_type, message, severity) 
                               VALUES (NOW(), 'Arduino Status', '$alertMessage', 
                               '" . ($arduinoStatus === "ONLINE" ? "info" : "warning") . "')");
                } catch (Exception $e) {
                }
            } catch (Exception $e) {
            }
        }
        
        $response["arduino"] = $arduinoStatus;
    } catch (Exception $e) {
    }
} catch (Exception $e) {
    $response["db"] = "OFFLINE";
}

// 2. Test Google (8.8.8.8 port 53)
$connected = @fsockopen("8.8.8.8", 53, $errno, $errstr, 1);
if ($connected) {
    $response["google"] = "ONLINE";
    fclose($connected);
}

echo json_encode($response);