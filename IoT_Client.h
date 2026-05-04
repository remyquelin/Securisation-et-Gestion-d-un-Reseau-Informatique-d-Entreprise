#ifndef IOT_CLIENT_H
#define IOT_CLIENT_H

#include <string>

class IoT_Client {
private:
    std::string serverURL;

public:
    IoT_Client();
    ~IoT_Client();
    bool connect(); // Vérifie si le serveur répond
    void sendPayload(std::string data); // Envoie les données (temp et porte)
};

#endif