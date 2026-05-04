<?php
// Central database configuration
$dbHost = '192.168.1.12';
$dbName = 'securite_local';
$dbUser = 'arduino_user';
$dbPass = 'votre_mot_de_passe';

function getPDO(array $opts = []) {
    global $dbHost, $dbName, $dbUser, $dbPass;
    $defaultOpts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];
    $options = $opts + $defaultOpts;
    return new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUser, $dbPass, $options);
}

?>
