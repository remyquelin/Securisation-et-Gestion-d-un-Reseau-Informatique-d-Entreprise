#ifndef IOT_CLIENT_H
#define IOT_CLIENT_H

#include <string>

// ─── Classe IoT_Client ────────────────────────────────────────────────────────
class IoT_Client {
private:
    int         port;
    std::string serverIP;

public:
    IoT_Client(const std::string& serverIP, int port);
    ~IoT_Client();

    bool connect();                        // Vérifie que le serveur est joignable
    void sendPayload(std::string payload); // Envoie les données via HTTP GET
};

#endif
