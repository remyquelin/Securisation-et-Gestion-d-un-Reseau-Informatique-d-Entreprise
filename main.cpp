// =============================================================================
// main.cpp
// Point d'entrée du programme. Contient la boucle principale.
// Utilise les classes Sensor_DHT22 et Sensor_Porte avec héritage.
// =============================================================================

#include "Sensor.h"       // Pour Sensor_DHT22 et Sensor_Porte
#include "IoT_Client.h"   // Pour connecter_serveur() et envoyer_donnees()
#include <chrono>         // Pour la gestion du temps et des intervalles
#include <iostream>       // Pour std::cout
#include <string>         // Pour std::string et std::to_string()
#include <unistd.h>       // Pour sleep() (pause en secondes)
#include <wiringPi.h>     // Pour wiringPiSetup()


// =============================================================================
// DÉFINITION DES BROCHES
// Numérotation wiringPi (PAS GPIO BCM).
// Tape "gpio readall" dans le terminal pour voir la correspondance.
// =============================================================================
const int BROCHE_DHT22 = 7;   // wiringPi 7 = GPIO 4
const int BROCHE_PORTE = 0;   // wiringPi 0 = GPIO 17


int main() {

    // -------------------------------------------------------------------------
    // Initialisation de wiringPi
    // Doit être appelée UNE SEULE FOIS au démarrage, avant tout digitalWrite/Read.
    // Nécessite d'être lancé avec 'sudo'.
    // -------------------------------------------------------------------------
    if (wiringPiSetup() == -1) {
        std::cout << "[ERREUR] Impossible d'initialiser wiringPi !" << std::endl;
        std::cout << "         Relancez avec : sudo ./capteurs_bts" << std::endl;
        return 1; // On quitte avec un code d'erreur
    }

    // DÉLAI DE STABILISATION : Le DHT22 a besoin de temps pour se réveiller
    // et entrer en mode de communication après le démarrage du Raspberry Pi.
    // Sans ce délai, la première lecture timeout toujours.
    sleep(2);

    // Connexion au serveur (facultatif pour l'instant, peut être commenté)
    bool serveur_disponible = connecter_serveur();
    if (!serveur_disponible) {
        std::cout << "[IoT] Serveur indisponible, les donnees ne seront pas envoyees." << std::endl;
    }

    // -------------------------------------------------------------------------
    // Création et initialisation des capteurs (instances des classes)
    // -------------------------------------------------------------------------
    Sensor_DHT22 dht22;
    Sensor_Porte porte;
    
    dht22.initialiser(BROCHE_DHT22);
    porte.initialiser(BROCHE_PORTE);
    porte.setInversion(true); // Inversion activée car le capteur de porte est inversé sur ton montage

    std::cout << "=============================================" << std::endl;
    std::cout << "   Systeme BTS - Surveillance Active         " << std::endl;
    std::cout << "   DHT22 -> broche wiringPi " << BROCHE_DHT22   << std::endl;
    std::cout << "   Porte -> broche wiringPi " << BROCHE_PORTE   << std::endl;
    std::cout << "=============================================" << std::endl;

    // -------------------------------------------------------------------------
    // Envoi toutes les 5 minutes, et immédiatement en cas de changement d'état
    // du capteur de porte.
    // On ne relit le DHT22 que toutes les 2 secondes minimum.
    // -------------------------------------------------------------------------
    auto dernier_envoi = std::chrono::steady_clock::now() - std::chrono::seconds(300);
    auto dernier_lecture_dht = std::chrono::steady_clock::now() - std::chrono::seconds(2);
    ResultatDHT22 derniere_mesure_valide = { 0.0f, 0.0f, false };
    
    // Première lecture pour initialiser l'état précédent
    porte.lire();
    bool etat_porte_precedent = porte.estOuverte();

    while (true) {
        // Lire l'état de la porte
        porte.lire();
        bool porte_ouverte = porte.estOuverte();
        bool changement_porte = (porte_ouverte != etat_porte_precedent);
        auto maintenant = std::chrono::steady_clock::now();
        bool intervalle_ecoule = (maintenant - dernier_envoi) >= std::chrono::seconds(300);

        if (changement_porte || intervalle_ecoule) {
            std::cout << "\n--- Envoi de donnees "
                      << (changement_porte ? "(changement de porte)" : "(intervalle 5 minutes)")
                      << " ---" << std::endl;
            std::cout << "Porte    : " << (porte_ouverte ? "OUVERTE" : "FERMEE") << std::endl;

            bool lecture_dht = (maintenant - dernier_lecture_dht) >= std::chrono::seconds(2);
            ResultatDHT22 mesure = derniere_mesure_valide;

            if (lecture_dht) {
                dht22.lire();
                mesure = dht22.obtenirMesure();
                dernier_lecture_dht = maintenant;
                if (mesure.succes) {
                    derniere_mesure_valide = mesure;
                } else if (derniere_mesure_valide.succes) {
                    std::cout << "[INFO] Utilisation de la derniere mesure DHT valide." << std::endl;
                    mesure = derniere_mesure_valide;
                }
            } else if (derniere_mesure_valide.succes) {
                std::cout << "[INFO] Lecture DHT reportee; utilisation de la derniere mesure valide." << std::endl;
            }

            if (mesure.succes) {
                std::cout << "Temp     : " << mesure.temperature << " C" << std::endl;
                std::cout << "Humidite : " << mesure.humidite    << " %" << std::endl;
            } else {
                std::cout << "Temp     : Lecture echouee ou indisponible." << std::endl;
                std::cout << "Humidite : Lecture echouee ou indisponible." << std::endl;
            }

            std::string payload = "door=" + std::to_string(porte_ouverte ? 1 : 0);
            if (mesure.succes) {
                payload += "&temp=" + std::to_string(mesure.temperature);
                payload += "&hum="  + std::to_string(mesure.humidite);
            }

            if (serveur_disponible) {
                envoyer_donnees(payload);
            } else {
                std::cout << "[IoT] Envoi ignore - serveur non accessible." << std::endl;
            }

            dernier_envoi = maintenant;
            etat_porte_precedent = porte_ouverte;
        }

        sleep(1);
    }

    return 0;
}
