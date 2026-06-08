<?php
// API pour récupérer la dernière photo
header('Content-Type: application/json');
include 'db_config.php';

try {
    $pdo = getPDO();
    
    // Récupérer la dernière photo
    $stmt = $pdo->query("SELECT id, timestamp, photo_url, photo_name, device_name, description 
                         FROM photos_portier 
                         ORDER BY timestamp DESC 
                         LIMIT 1");
    
    $photo = $stmt->fetch();
    
    if ($photo) {
        echo json_encode([
            'success' => true,
            'photo' => [
                'id' => $photo['id'],
                'timestamp' => $photo['timestamp'],
                'url' => $photo['photo_url'],
                'name' => $photo['photo_name'],
                'device' => $photo['device_name'],
                'description' => $photo['description']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune photo trouvée'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
