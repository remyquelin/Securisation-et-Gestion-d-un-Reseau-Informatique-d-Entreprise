#include "IoT_Client.h"
#include <iostream>
#include <curl/curl.h>

// ─── Constructeur ─────────────────────────────────────────────────────────────
IoT_Client::IoT_Client(const std::string& serverIP, int port)
    : serverIP(serverIP), port(port) {}

// ─── Destructeur ─────────────────────────────────────────────────────────────
IoT_Client::~IoT_Client() {}

// ─── connect() ───────────────────────────────────────────────────────────────
bool IoT_Client::connect() {
    std::cout << "[IoT_Client] Connexion vers " << serverIP
              << ":" << port << " ..." << std::endl;

    // Tentative d'accès à la racine du serveur pour vérifier qu'il répond
    CURL* curl = curl_easy_init();
    if (!curl) return false;

    std::string url = "http://" + serverIP + ":" + std::to_string(port) + "/";
    curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
    curl_easy_setopt(curl, CURLOPT_NOBODY, 1L);          // HEAD request
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 3L);         // 3 secondes max

    CURLcode res = curl_easy_perform(curl);
    curl_easy_cleanup(curl);

    bool ok = (res == CURLE_OK);
    std::cout << "[IoT_Client] " << (ok ? "Serveur joignable." : "Serveur inaccessible !") << std::endl;
    return ok;
}

// ─── sendPayload() ────────────────────────────────────────────────────────────
void IoT_Client::sendPayload(std::string payload) {
    CURL* curl = curl_easy_init();
    if (!curl) return;

    // Construction de l'URL : http://IP:PORT/post_data.php?temp=XX&door=Y
    std::string url = "http://" + serverIP + ":" + std::to_string(port)
                    + "/post_data.php?" + payload;

    curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
    curl_easy_setopt(curl, CURLOPT_HTTPGET, 1L);
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 5L);

    CURLcode res = curl_easy_perform(curl);
    if (res != CURLE_OK) {
        std::cerr << "[IoT_Client] Erreur envoi : " << curl_easy_strerror(res) << std::endl;
    }
    curl_easy_cleanup(curl);
}
