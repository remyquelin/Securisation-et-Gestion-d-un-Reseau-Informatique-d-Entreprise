<?php
@header('Content-Type: application/json');

$response = array(
    "db" => "OFFLINE",
    "google" => "OFFLINE",
    "arduino" => "OFFLINE"
);

// 1. Test MariaDB (192.168.1.12)
$dbHost = '192.168.1.12';
$dbName = 'securite_local';
$dbUser = 'arduino_user';
$dbPass = 'votre_mot_de_passe';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8",
        $dbUser,
        $dbPass,
        array(PDO::ATTR_TIMEOUT => 2, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    $response["db"] = "ONLINE";
} catch (PDOException $e) {
    $response["db"] = "OFFLINE";
}

// 2. Test Raspberry Pi (192.168.1.13) - Port 22 (SSH)
$raspSocket = @fsockopen("192.168.1.13", 22, $errno, $errstr, 2);
if ($raspSocket) {
    $response["arduino"] = "ONLINE";
    @fclose($raspSocket);
}

// 3. Test Google (8.8.8.8 port 53)
$googleSocket = @fsockopen("8.8.8.8", 53, $errno, $errstr, 2);
if ($googleSocket) {
    $response["google"] = "ONLINE";
    @fclose($googleSocket);
}

echo json_encode($response);
exit;