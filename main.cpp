#include "Sensor.h"
#include "IoT_Client.h"
#include <iostream>
#include <unistd.h> 
#include <cmath> // Pour std::isnan et std::abs

int main() {
    // 1. Initialisation des composants
    // Pin 0 wiringPi = Pin 11 physique (Porte)
    DoorSensor maPorte(0);
    // Pin 7 wiringPi = Pin 7 physique (DHT22)
    DHT22Sensor monAir(7);
    // Client vers le Docker Web (192.168.1.11)
    IoT_Client monClient;

    maPorte.begin();
    monAir.begin();
    monClient.connect();

    // 2. Variables de mémoire pour détecter les changements
    float dernierEtatPorte = -1.0f;
    float derniereTemp = -100.0f;
    const float SEUIL_TEMP = 0.5f; // On n'envoie la temp que si elle bouge de 0.5°C

    std::cout << "--- Système Alarme BTS : Surveillance Active ---" << std::endl;
    std::cout << "Cible : http://192.168.1.11/post_data.php" << std::endl;

    while (true) {
        // 3. Lecture des capteurs
        float porteActuelle = maPorte.readValue();
        float tempLue = monAir.readValue();

        // 4. Sécurité pour le DHT22 (si NaN, on garde la dernière valeur connue)
        float tempAEnvoyer;
        if (std::isnan(tempLue)) {
            // Si c'est la toute première lecture et qu'elle échoue, on met 21.0 par défaut
            tempAEnvoyer = (derniereTemp == -100.0f) ? 21.0f : derniereTemp;
        }
        else {
            tempAEnvoyer = tempLue;
        }

        // 5. Logique de détection de changement
        bool porteAChange = (porteActuelle != dernierEtatPorte);
        bool tempAChange = (std::abs(tempAEnvoyer - derniereTemp) >= SEUIL_TEMP);

        // 6. Envoi si nécessaire
        if (porteAChange || tempAChange) {

            std::cout << "\n[EVENT] Changement détecté :" << std::endl;
            std::cout << "  - Porte : " << (porteActuelle > 0.5 ? "FERMEE" : "OUVERTE") << std::endl;
            std::cout << "  - Temp  : " << tempAEnvoyer << " C" << std::endl;

            // Construction du payload pour post_data.php?temp=XX&door=Y
            std::string payload = "temp=" + std::to_string(tempAEnvoyer) +
                "&door=" + std::to_string((int)porteActuelle);

            // Envoi au serveur Web
            monClient.sendPayload(payload);

            // Mise à jour de la mémoire
            dernierEtatPorte = porteActuelle;
            derniereTemp = tempAEnvoyer;

            std::cout << "[SERVEUR] Données transmises avec succès." << std::endl;
        }

        // 7. Petite pause pour ne pas saturer le processeur
        // 500ms permet d'être réactif sur la porte sans bloquer le système
        usleep(500000);
    }

    return 0;
}