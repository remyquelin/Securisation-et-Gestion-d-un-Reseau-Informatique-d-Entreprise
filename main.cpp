#include "Sensor.h"
#include "IoT_Client.h"
#include <iostream>
#include <cmath>
#include <unistd.h> // usleep

int main() {
    // ── Instanciation des capteurs et du client ───────────────────────────────
    // Pin wiringPi 0 = GPIO 17 physique  → capteur de porte (reed switch)
    // Pin wiringPi 7 = GPIO 11 physique  → capteur DHT22
    Sensor_Porte  maPorte(0);
    Sensor_DHT22  monAir(7);
    IoT_Client    monClient("192.168.1.11", 80);

    monClient.connect();

    // ── Variables pour détecter les changements ───────────────────────────────
    float dernierEtatPorte = -1.0f;
    float derniereTemp     = -100.0f;
    const float SEUIL_TEMP = 0.5f; // Envoi seulement si variation ≥ 0.5 °C

    std::cout << "--- Système IoT : Surveillance Active ---" << std::endl;

    while (true) {
        // Lecture réelle des capteurs
        float porte = maPorte.readValue();
        float temp  = monAir.readValue();

        // Si le DHT22 échoue (NAN), on garde la dernière température connue
        if (std::isnan(temp)) {
            temp = (derniereTemp == -100.0f) ? 21.0f : derniereTemp;
        }

        // Détection de changement
        bool porteAChange = (porte != dernierEtatPorte);
        bool tempAChange  = (std::abs(temp - derniereTemp) >= SEUIL_TEMP);

        if (porteAChange || tempAChange) {
            std::cout << "\n[EVENT] Changement détecté :" << std::endl;
            std::cout << "  Porte : " << (porte > 0.5f ? "OUVERTE" : "FERMEE") << std::endl;
            std::cout << "  Temp  : " << temp << " °C" << std::endl;

            // Envoi : post_data.php?temp=XX.X&door=Y
            std::string payload = "temp=" + std::to_string(temp)
                                + "&door=" + std::to_string((int)porte);
            monClient.sendPayload(payload);

            dernierEtatPorte = porte;
            derniereTemp     = temp;

            std::cout << "[OK] Données envoyées." << std::endl;
        }

        usleep(500000); // 500 ms
    }

    return 0;
}
