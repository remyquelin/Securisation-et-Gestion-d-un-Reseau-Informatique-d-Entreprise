#ifndef SENSOR_H
#define SENSOR_H

// =============================================================================
// Sensor.h
// Déclarations des classes de capteurs avec héritage.
// =============================================================================

#include <cmath> // Pour NAN


// =============================================================================
// STRUCTURE ResultatDHT22
// Regroupe les trois informations renvoyées par la lecture DHT22.
// =============================================================================
struct ResultatDHT22 {
    float temperature;  // Température lue en degrés Celsius
    float humidite;     // Humidité relative lue en pourcentage
    bool  succes;       // true = lecture OK, false = erreur (checksum, timeout...)
};


// =============================================================================
// CLASSE DE BASE : Sensor
// Classe abstraite définissant l'interface commune des capteurs
// =============================================================================
class Sensor {
public:
    virtual ~Sensor() = default;
    
    // Méthode virtuelle pure : chaque capteur implémente sa propre lecture
    virtual void lire() = 0;
    
    // Initialiser la broche
    virtual void initialiser(int broche) { this->broche = broche; }
    
protected:
    int broche;  // Numéro de broche wiringPi
};


// =============================================================================
// CLASSE : Sensor_DHT22
// Hérite de Sensor, lit la température et l'humidité
// =============================================================================
class Sensor_DHT22 : public Sensor {
public:
    Sensor_DHT22() : derniere_mesure({ NAN, NAN, false }) {}
    
    void initialiser(int broche) override;
    void lire() override;
    
    // Accesseurs pour les valeurs lues
    ResultatDHT22 obtenirMesure() const { return derniere_mesure; }
    float obtenirTemperature() const { return derniere_mesure.temperature; }
    float obtenirHumidite() const { return derniere_mesure.humidite; }
    bool estValide() const { return derniere_mesure.succes; }
    
private:
    ResultatDHT22 derniere_mesure;
    ResultatDHT22 lire_dht22_interne();
};


// =============================================================================
// CLASSE : Sensor_Porte
// Hérite de Sensor, lit l'état du contact sec (porte ouverte/fermée)
// =============================================================================
class Sensor_Porte : public Sensor {
public:
    Sensor_Porte() : etat_porte(false), inversion(false) {}
    
    void initialiser(int broche) override;
    void lire() override;
    
    // Accesseurs pour l'état
    bool estOuverte() const { return etat_porte; }
    bool obtenirEtat() const { return etat_porte; }
    void setInversion(bool inversion_active) { inversion = inversion_active; }
    bool estInversee() const { return inversion; }
    
private:
    bool etat_porte;  // true = OUVERTE, false = FERMÉE
    bool inversion;   // true = inverse le sens du capteur
};


#endif // SENSOR_H
