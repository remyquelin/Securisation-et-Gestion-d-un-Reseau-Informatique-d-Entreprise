<?php
// Si la page n'est pas définie dans l'URL, par défaut afficher la page d'accueil
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Liste des pages autorisées, pour éviter des erreurs de routage
$allowed_pages = ['dashboard', 'cameras', 'capteur', 'events'];

// Vérifier si la page demandée est valide, sinon rediriger vers une page d'erreur
if (!in_array($page, $allowed_pages)) {
    // Rediriger vers la page d'erreur ou la page d'accueil si la page n'existe pas
    header("Location: index.php?page=dashboard");
    exit;
}

// Rediriger vers la page appropriée
switch ($page) {
    case 'dashboard':
        header("Location: dashboard.php");
        break;
    case 'cameras':
        header("Location: cameras.php");
        break;
    case 'capteur':
        header("Location: capteur.php");
        break;
    case 'events':
        header("Location: events.php");
        break;
}

// Rediriger le navigateur vers la page correcte
exit;
