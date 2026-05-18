#ifndef SENSOR_H
#define SENSOR_H

#include <string>

// ─── Classe de base : Sensor ────────────────────────────────────────────────
class Sensor {
protected:
    int         pin;
    std::string type;

public:
    Sensor(int pin, const std::string& type);
    virtual ~Sensor();

    virtual float readValue() = 0; // méthode virtuelle pure
};

// ─── Sous-classe : Sensor_DHT22 ─────────────────────────────────────────────
class Sensor_DHT22 : public Sensor {
public:
    Sensor_DHT22(int pin);
    float readValue() override; // retourne la température en °C
};

// ─── Sous-classe : Sensor_Porte ─────────────────────────────────────────────
class Sensor_Porte : public Sensor {
public:
    Sensor_Porte(int pin);
    float readValue() override; // retourne 1.0 (ouverte) ou 0.0 (fermée)
};

#endif
