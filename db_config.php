<?php
// Configuration centrale de la base de données
$dbHost = '192.168.1.12';
$dbName = 'securite_local';
$dbUser = 'arduino_user';
$dbPass = 'votre_mot_de_passe';

function getPDO(array $opts = []) {
    global $dbHost, $dbName, $dbUser, $dbPass;
    
    $defaultOpts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        // Optimisation : Connexion persistante pour le polling intensif
        PDO::ATTR_PERSISTENT         => true, 
        PDO::ATTR_TIMEOUT            => 2
    ];
    
    $options = $opts + $defaultOpts;
    
    try {
        return new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUser, $dbPass, $options);
    } catch (PDOException $e) {
        // En prod, on log l'erreur discrètement
        error_log($e->getMessage());
        return null;
    }
}
?>