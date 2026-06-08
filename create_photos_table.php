<?php
// Script de création de la table photos dans la BDD
include 'db_config.php';

try {
    $pdo = getPDO();
    
    $sql = "CREATE TABLE IF NOT EXISTS photos_portier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        photo_url VARCHAR(500) NOT NULL COMMENT 'URL ou chemin de la photo',
        photo_name VARCHAR(255) NOT NULL COMMENT 'Nom du fichier',
        device_name VARCHAR(100) COMMENT 'Nom du portier/caméra',
        description TEXT COMMENT 'Description optionnelle'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    CREATE INDEX idx_timestamp ON photos_portier(timestamp DESC);";
    
    $pdo->exec($sql);
    echo "✅ Table 'photos_portier' créée avec succès!";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
