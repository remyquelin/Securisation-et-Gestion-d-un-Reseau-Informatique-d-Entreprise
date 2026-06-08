#ifndef IOT_CLIENT_H
#define IOT_CLIENT_H

// =============================================================================
// IoT_Client.h
// Déclarations des fonctions d'envoi de données vers le serveur.
// On garde ce fichier séparé pour que tu puisses facilement activer/désactiver
// la partie réseau sans toucher au reste du code.
// =============================================================================

#include <string> // Pour std::string


// -----------------------------------------------------------------------------
// DÉCLARATIONS DES FONCTIONS (définitions dans IoT_Client.cpp)
// -----------------------------------------------------------------------------

// Tente une connexion au serveur et affiche un message de confirmation.
// Retourne true si le serveur est joignable, false sinon.
bool connecter_serveur();

// Envoie une chaîne de données (payload) au serveur via une requête HTTP GET.
// Exemple de payload : "door=1&temp=22.5&hum=58.3"
// La fonction construit l'URL complète et utilise cURL pour l'envoyer.
void envoyer_donnees(const std::string& payload);


#endif // IOT_CLIENT_H
