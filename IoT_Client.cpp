#include "IoT_Client.h"
#include <iostream>
#include <curl/curl.h>

// Le constructeur
IoT_Client::IoT_Client() {
    serverURL = "http://192.168.1.11/post_data.php";
}

// LE DESTRUCTEUR (C'est lui qui manque selon l'erreur !)
IoT_Client::~IoT_Client() {
}

// LA FONCTION CONNECT (Elle manque aussi !)
bool IoT_Client::connect() {
    std::cout << "Tentative de connexion au serveur Web (.11)..." << std::endl;
    return true;
}

// La fonction d'envoi
void IoT_Client::sendPayload(std::string data) {
    CURL* curl = curl_easy_init();
    if (curl) {
        std::string fullURL = serverURL + "?" + data;
        curl_easy_setopt(curl, CURLOPT_URL, fullURL.c_str());
        curl_easy_setopt(curl, CURLOPT_HTTPGET, 1L);
        CURLcode res = curl_easy_perform(curl);
        curl_easy_cleanup(curl);
    }
}