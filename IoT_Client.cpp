// =============================================================================
// IoT_Client.cpp
// Implémentation des fonctions d'envoi de données vers le serveur HTTP.
// Utilise la bibliothèque cURL pour effectuer des requêtes HTTP GET simples.
//
// NOTE POUR LE BTS : Cette partie est volontairement mise de côté pour l'instant.
// Le code est présent mais tu peux ne pas compiler avec -lcurl et ne pas appeler
// ces fonctions dans main.cpp tant que tu n'en as pas besoin.
// =============================================================================

#include "IoT_Client.h"
#include <iostream>
#include <curl/curl.h>  // Bibliothèque cURL pour les requêtes HTTP


// Adresse du serveur PHP qui reçoit les données
// Modifie cette ligne si l'IP ou le chemin de ton serveur change
const std::string URL_SERVEUR = "http://192.168.1.11/post_data.php";


// =============================================================================
// FONCTION : connecter_serveur()
// Vérifie simplement que la bibliothèque cURL est disponible et affiche
// un message. Pour une vraie vérification, on pourrait faire un ping HTTP,
// mais pour débuter ce message suffit.
// =============================================================================
bool connecter_serveur() {
    std::cout << "[IoT] Connexion au serveur : " << URL_SERVEUR << std::endl;

    // curl_easy_init() initialise une session cURL.
    // Si elle retourne NULL, cURL n'est pas disponible sur ce système.
    CURL* curl = curl_easy_init();
    if (!curl) {
        std::cout << "[ERREUR IoT] Impossible d'initialiser cURL !" << std::endl;
        return false;
    }

    // On nettoie immédiatement, c'était juste un test d'initialisation
    curl_easy_cleanup(curl);

    std::cout << "[IoT] cURL OK, pret a envoyer des donnees." << std::endl;
    return true;
}


// =============================================================================
// FONCTION : envoyer_donnees()
// Envoie le payload au serveur via une requête HTTP GET.
// Le payload est ajouté à l'URL sous forme de paramètres :
//   http://192.168.1.11/post_data.php?door=1&temp=22.5&hum=58.3
// =============================================================================
void envoyer_donnees(const std::string& payload) {

    // Construit l'URL complète en ajoutant "?" + les paramètres
    std::string url_complete = URL_SERVEUR + "?" + payload;

    std::cout << "[IoT] Envoi vers : " << url_complete << std::endl;

    // Initialise une session cURL
    CURL* curl = curl_easy_init();
    if (!curl) {
        std::cout << "[ERREUR IoT] Impossible d'initialiser cURL pour l'envoi." << std::endl;
        return;
    }

    // Définit l'URL cible
    curl_easy_setopt(curl, CURLOPT_URL, url_complete.c_str());

    // On utilise une requête GET (méthode HTTP la plus simple)
    curl_easy_setopt(curl, CURLOPT_HTTPGET, 1L);

    // Définit un timeout de 5 secondes pour éviter de bloquer la boucle principale
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 5L);

    // Désactive l'affichage de la réponse du serveur dans le terminal
    // (par défaut cURL affiche la réponse HTML, ce qui pollue la console)
    curl_easy_setopt(curl, CURLOPT_NOBODY, 1L);

    // Exécute la requête HTTP
    CURLcode resultat = curl_easy_perform(curl);

    // Vérifie si la requête a réussi
    if (resultat != CURLE_OK) {
        std::cout << "[ERREUR IoT] Echec de l'envoi : "
                  << curl_easy_strerror(resultat) << std::endl;
    } else {
        std::cout << "[IoT] Donnees envoyees avec succes." << std::endl;
    }

    // IMPORTANT : toujours libérer la mémoire allouée par cURL
    curl_easy_cleanup(curl);
}
