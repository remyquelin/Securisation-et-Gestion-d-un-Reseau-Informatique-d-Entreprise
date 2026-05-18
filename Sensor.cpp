#include "Sensor.h"
#include <wiringPi.h>
#include <iostream>
#include <cmath>
#include <cstdint>

// ─── Initialisation wiringPi (une seule fois) ────────────────────────────────
static void initWiringPi() {
    static bool done = false;
    if (!done) {
        if (wiringPiSetup() == -1) {
            std::cerr << "[ERREUR] wiringPiSetup() a échoué.\n";
        }
        done = true;
    }
}

// ─── Sensor ──────────────────────────────────────────────────────────────────
Sensor::Sensor(int pin, const std::string& type)
    : pin(pin), type(type) {
    initWiringPi();
}

Sensor::~Sensor() {}

// ─── Sensor_DHT22 ────────────────────────────────────────────────────────────
Sensor_DHT22::Sensor_DHT22(int pin) : Sensor(pin, "DHT22") {}

float Sensor_DHT22::readValue() {
    uint8_t data[5] = {0, 0, 0, 0, 0};

    // Signal de démarrage : tirer LOW > 1 ms, puis relâcher
    pinMode(pin, OUTPUT);
    digitalWrite(pin, LOW);
    delay(2);
    digitalWrite(pin, HIGH);
    delayMicroseconds(30);
    pinMode(pin, INPUT);

    // Attente de la réponse du capteur (LOW ~80µs puis HIGH ~80µs)
    unsigned int cnt = 0;
    while (digitalRead(pin) == HIGH) { if (++cnt > 1000) return NAN; delayMicroseconds(1); }
    cnt = 0;
    while (digitalRead(pin) == LOW)  { if (++cnt > 1000) return NAN; delayMicroseconds(1); }
    cnt = 0;
    while (digitalRead(pin) == HIGH) { if (++cnt > 1000) return NAN; delayMicroseconds(1); }

    // Lecture des 40 bits
    for (int i = 0; i < 40; ++i) {
        // Attendre la montée
        cnt = 0;
        while (digitalRead(pin) == LOW) { if (++cnt > 1000) return NAN; delayMicroseconds(1); }
        // Mesurer la durée du HIGH : > 40µs → bit '1', sinon bit '0'
        unsigned int len = 0;
        while (digitalRead(pin) == HIGH) { ++len; delayMicroseconds(1); if (len > 200) break; }
        data[i / 8] <<= 1;
        if (len > 40) data[i / 8] |= 1;
    }

    // Vérification du checksum
    if ((uint8_t)(data[0] + data[1] + data[2] + data[3]) != data[4])
        return NAN;

    // Décodage de la température
    int tempRaw = ((data[2] & 0x7F) << 8) | data[3];
    float temp  = tempRaw * 0.1f;
    if (data[2] & 0x80) temp = -temp;

    return temp;
}

// ─── Sensor_Porte ─────────────────────────────────────────────────────────────
Sensor_Porte::Sensor_Porte(int pin) : Sensor(pin, "Porte") {
    pinMode(pin, INPUT);
    pullUpDnControl(pin, PUD_UP); // résistance pull-up interne
}

float Sensor_Porte::readValue() {
    // LOW = contact fermé (interrupteur relié à GND) → porte FERMÉE (0)
    // HIGH = contact ouvert                           → porte OUVERTE (1)
    return (digitalRead(pin) == HIGH) ? 1.0f : 0.0f;
}
