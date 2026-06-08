<?php
// Récupère la liste de toutes les photos du portier des dernières 24 heures

$dir = "intercom/photos/";
$files = glob($dir . "*.jpg");

if (empty($files)) {
    echo json_encode(['photos' => []]);
    exit;
}

$now = time();
$photos = [];

// Nettoie et récupère les photos
foreach ($files as $file) {
    $mtime = filemtime($file);
    // Garde seulement les photos de moins de 24 heures
    if ($now - $mtime <= 86400) {
        $photos[] = [
            'url' => $file,
            'time' => $mtime,
            'name' => basename($file)
        ];
    } else {
        // Supprime les anciennes photos
        @unlink($file);
    }
}

// Trie par date (plus récentes d'abord)
usort($photos, function($a, $b) {
    return $b['time'] - $a['time'];
});

// Garde les 10 dernières
$photos = array_slice($photos, 0, 10);

// Retourne en JSON
header('Content-Type: application/json');
echo json_encode(['photos' => $photos]);
?>
