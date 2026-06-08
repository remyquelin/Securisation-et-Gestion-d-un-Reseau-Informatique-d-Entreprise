// =============================================================================
// Sensor.cpp
// Implémentation des classes Sensor_DHT22 et Sensor_Porte avec héritage.
// =============================================================================

#include "Sensor.h"       // Nos déclarations (Sensor, Sensor_DHT22, Sensor_Porte)
#include <iostream>       // Pour std::cout (affichage des messages d'erreur)
#include <cstdint>        // Pour uint8_t (entier 8 bits non signé), int16_t
#include <cmath>          // Pour NAN (Not A Number)
#include <wiringPi.h>     // Bibliothèque wiringPi : contrôle des GPIO du Raspberry Pi


// =============================================================================
// CLASSE SENSOR_DHT22 - Implémentation
// =============================================================================

void Sensor_DHT22::initialiser(int broche) {
    Sensor::initialiser(broche);
    derniere_mesure = { NAN, NAN, false };
}

void Sensor_DHT22::lire() {
    derniere_mesure = lire_dht22_interne();
}

// =============================================================================
// FONCTION INTERNE : lire_dht22_interne()
// Implémentation du protocole DHT22 - lire le capteur DHT22.
//
// --- PROTOCOLE DHT22 (résumé) ---
//
// Le DHT22 communique sur UNE seule broche en envoyant des impulsions.
// La durée de chaque impulsion détermine si c'est un bit '0' ou '1'.
//
//  Étape 1 - Start : Le Pi envoie LOW 18ms puis HIGH → le capteur se réveille.
//  Étape 2 - Réponse : Le capteur répond LOW ~80µs puis HIGH ~80µs.
//  Étape 3 - 40 bits : Pour chaque bit :
//              LOW ~50µs (préambule)
//              puis HIGH ~26µs  → bit '0'
//              puis HIGH ~70µs  → bit '1'
//  Étape 4 - Checksum : octet[0]+[1]+[2]+[3] doit égaler octet[4].
//
//  Format des 40 bits :
//    [8 bits hum. entier] [8 bits hum. déc.] [8 bits temp. entier]
//    [8 bits temp. déc.] [8 bits checksum]
// =============================================================================
ResultatDHT22 Sensor_DHT22::lire_dht22_interne() {

    // Résultat par défaut : échec avec des valeurs invalides
    ResultatDHT22 resultat = { NAN, NAN, false };

    // Tableau de 5 octets (40 bits) qui contiendra les données brutes
    uint8_t donnees[5] = { 0, 0, 0, 0, 0 };

    // -------------------------------------------------------------------------
    // ÉTAPE 1 : Signal de démarrage envoyé par le Raspberry Pi
    // -------------------------------------------------------------------------

    // La broche en SORTIE : c'est nous qui parlons
    pinMode(broche, OUTPUT);

    // Assure que la ligne est bien haute avant le signal de démarrage
    digitalWrite(broche, HIGH);
    delay(10);

    // On tire la broche à LOW pendant 18ms pour "sonner" le capteur
    digitalWrite(broche, LOW);
    delay(18);                  // delay() wiringPi : pause en millisecondes

    // On repasse à HIGH pour signaler la fin de notre signal
    digitalWrite(broche, HIGH);
    delayMicroseconds(100);     // Pause en HIGH : laisse le temps au capteur de réagir

    // La broche en ENTRÉE : maintenant le capteur prend la parole
    pinMode(broche, INPUT);
    pullUpDnControl(broche, PUD_UP); // Assure que la ligne reste haute quand le capteur ne parle pas

    // -------------------------------------------------------------------------
    // ÉTAPE 2 : Attendre la réponse du DHT22 (LOW ~80µs puis HIGH ~80µs)
    // -------------------------------------------------------------------------
    int timeout = 0;

    // On attend le LOW initial de réponse
    while (digitalRead(broche) == HIGH) {
        delayMicroseconds(1);
        if (++timeout > 100) {
            std::cout << "[ERREUR DHT22] Timeout etape 2a (pas de reponse)" << std::endl;
            return resultat;
        }
    }

    timeout = 0;
    // On attend la fin du LOW (~80µs)
    while (digitalRead(broche) == LOW) {
        delayMicroseconds(1);
        if (++timeout > 100) {
            std::cout << "[ERREUR DHT22] Timeout etape 2b" << std::endl;
            return resultat;
        }
    }

    timeout = 0;
    // On attend la fin du HIGH (~80µs) : après ça, les données commencent
    while (digitalRead(broche) == HIGH) {
        delayMicroseconds(1);
        if (++timeout > 100) {
            std::cout << "[ERREUR DHT22] Timeout etape 2c" << std::endl;
            return resultat;
        }
    }

    // -------------------------------------------------------------------------
    // ÉTAPE 3 : Lire les 40 bits un par un
    // -------------------------------------------------------------------------
    for (int i = 0; i < 40; i++) {

        // Chaque bit commence par un LOW ~50µs : on attend qu'il se termine
        timeout = 0;
        while (digitalRead(broche) == LOW) {
            delayMicroseconds(1);
            if (++timeout > 120) {
                std::cout << "[ERREUR DHT22] Timeout bit " << i << " (LOW attente)" << std::endl;
                return resultat;
            }
        }

        // On mesure combien de µs la broche reste en HIGH
        // C'est cette durée qui distingue '0' (~26µs) de '1' (~70µs)
        int duree_high = 0;
        while (digitalRead(broche) == HIGH) {
            delayMicroseconds(1);
            duree_high++;
            if (duree_high > 120) {
                std::cout << "[ERREUR DHT22] Timeout bit " << i << " (HIGH attente)" << std::endl;
                return resultat;
            }
        }

        // Quel octet et quel bit on remplit ?
        // i=0 → octet 0, bit 7 (MSB) ... i=7 → octet 0, bit 0 (LSB) ... etc.
        int index_octet = i / 8;
        int index_bit   = 7 - (i % 8);

        // Si le HIGH a duré plus de 40µs → c'est un '1', on met le bit à 1
        if (duree_high > 40) {
            donnees[index_octet] |= (1 << index_bit);
        }
        // Sinon c'est un '0', le bit reste à 0 (valeur initiale du tableau)
    }

    // -------------------------------------------------------------------------
    // ÉTAPE 4 : Vérification du checksum
    // La somme des 4 premiers octets (sur 8 bits) doit égaler le 5ème octet
    // -------------------------------------------------------------------------
    uint8_t checksum_calcule = (donnees[0] + donnees[1] + donnees[2] + donnees[3]) & 0xFF;

    if (checksum_calcule != donnees[4]) {
        std::cout << "[ERREUR DHT22] Checksum invalide ! (recu="
                  << (int)donnees[4] << " calcule=" << (int)checksum_calcule << ")" << std::endl;
        std::cout << "[DHT22] octets = "
                  << (int)donnees[0] << ", "
                  << (int)donnees[1] << ", "
                  << (int)donnees[2] << ", "
                  << (int)donnees[3] << ", "
                  << (int)donnees[4] << std::endl;
        return resultat;
    }

    // -------------------------------------------------------------------------
    // ÉTAPE 5 : Décoder les octets en valeurs lisibles
    // -------------------------------------------------------------------------

    float humidite_dht22 = ((donnees[0] << 8) | donnees[1]) / 10.0f;
    int16_t valeur_temp_brute = ((donnees[2] & 0x7F) << 8) | donnees[3];
    float temperature_dht22 = valeur_temp_brute / 10.0f;
    if (donnees[2] & 0x80) {
        temperature_dht22 = -temperature_dht22;
    }

    float humidite_dht11 = donnees[0] + donnees[1] / 10.0f;
    float temperature_dht11 = donnees[2] + donnees[3] / 10.0f;
    if (donnees[2] & 0x80) {
        temperature_dht11 = -temperature_dht11;
    }

    bool dht22_ok = (humidite_dht22 >= 0.0f && humidite_dht22 <= 100.0f &&
                     temperature_dht22 >= -40.0f && temperature_dht22 <= 80.0f);
    bool dht11_ok = (humidite_dht11 >= 0.0f && humidite_dht11 <= 100.0f &&
                     temperature_dht11 >= -40.0f && temperature_dht11 <= 80.0f);

    if (dht22_ok) {
        resultat.humidite = humidite_dht22;
        resultat.temperature = temperature_dht22;
    } else if (dht11_ok) {
        std::cout << "[INFO DHT] Donnees interpretees comme DHT11 : "
                  << "hum=" << humidite_dht11 << " temp=" << temperature_dht11 << std::endl;
        resultat.humidite = humidite_dht11;
        resultat.temperature = temperature_dht11;
    } else {
        std::cout << "[ERREUR DHT] Valeurs hors plage : "
                  << "hum_dht22=" << humidite_dht22 << " temp_dht22=" << temperature_dht22
                  << " | hum_dht11=" << humidite_dht11 << " temp_dht11=" << temperature_dht11
                  << std::endl;
        std::cout << "[DHT] octets bruts = "
                  << (int)donnees[0] << ", "
                  << (int)donnees[1] << ", "
                  << (int)donnees[2] << ", "
                  << (int)donnees[3] << ", "
                  << (int)donnees[4] << std::endl;
        return resultat;
    }

    resultat.succes = true;
    return resultat;
}


// =============================================================================
// CLASSE SENSOR_PORTE - Implémentation
// =============================================================================

void Sensor_Porte::initialiser(int broche) {
    Sensor::initialiser(broche);
    etat_porte = false;
    inversion = false;
}

void Sensor_Porte::lire() {
    // Configure la broche en ENTRÉE (on lit)
    pinMode(broche, INPUT);

    // Active la résistance pull-up interne (~50kΩ vers le 3.3V)
    // Sans ça, la broche "flotterait" et donnerait des valeurs aléatoires
    pullUpDnControl(broche, PUD_UP);

    // Lit la valeur : HIGH (1) ou LOW (0)
    int etat = digitalRead(broche);
    bool ouvert = (etat == HIGH);

    if (inversion) {
        ouvert = !ouvert;
    }

    etat_porte = ouvert;
}
