<?php
// Active l'affichage des erreurs pour le débug
error_reporting(E_ALL); 
ini_set('display_errors', 1);

require_once __DIR__ . '/db_config.php';

try {
    $pdo = getPDO();

    if(isset($_GET['temp']) && isset($_GET['door'])) {
        // Insertion Température
        $stmt1 = $pdo->prepare("INSERT INTO sensors_data (sensor_name, value, unit) VALUES ('Temperature', ?, 'C')");
        $stmt1->execute([$_GET['temp']]);

        // Insertion Porte
        $stmt2 = $pdo->prepare("INSERT INTO sensors_data (sensor_name, value, unit) VALUES ('Porte_Baie', ?, 'binary')");
        $stmt2->execute([$_GET['door']]);

        echo "Succès : Données insérées dans MariaDB";
    } else {
        echo "Erreur : Paramètres manquants dans l'URL";
    }
} catch (PDOException $e) {
    echo "Erreur de connexion ou SQL : " . $e->getMessage();
}
?>