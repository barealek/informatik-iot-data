#include <Arduino.h>
#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEScan.h>
#include <BLEAdvertisedDevice.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <iostream>
#include <nlohmann/json.hpp>

using json = nlohmann::json;

const char* ssid = "???";
const char* password = "???";

// Server til at modtage beaconsene at lede efter og sende de beaconsene der er i nærheden op
String serverName = "http://192.168.75.236:8080";

BLEScan* pBLEScan;

void setup() {
    Serial.begin(115200);

    WiFi.begin(ssid, password);
    Serial.println("Connecting to WiFi");
    while(WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("Connected to WiFi network with IP Address: ");
    Serial.println(WiFi.localIP());

    Serial.println("BLE scan starting");

    BLEDevice::init("");
    pBLEScan = BLEDevice::getScan(); 
    pBLEScan->setActiveScan(true);
    pBLEScan->setInterval(100);
    pBLEScan->setWindow(99);  
}

void loop() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;

        String serverBeaconRetrievePath = serverName + "/devices";

        http.begin(serverBeaconRetrievePath.c_str());

        int httpResponseCode = http.GET();

        if (httpResponseCode == 200) { 
            String response = http.getString();
            http.end();

            Serial.println("Found devices: ");
            Serial.println(response);

            json data = json::parse(response);

            std::vector<std::string> targetUUIDs;

            for (const auto& device : data) {
                if (device.contains("uuid")) {
                    targetUUIDs.push_back(device["uuid"].get<std::string>());
                }
            }

            Serial.println("--- Starting 5-Second Scan ---");
            
            BLEScanResults foundDevices = pBLEScan->start(5, false); 
            
            int count = foundDevices.getCount();
            Serial.printf("Scan complete. Found %d devices.\n", count);

            json payload = json::array({});  
            for (int i = 0; i < count; i++) {
                BLEAdvertisedDevice device = foundDevices.getDevice(i);
                
                if (device.haveManufacturerData()) {
                    std::string data = device.getManufacturerData();
                    
                    // iBeacon præfiks: 0x4C, 0x00, 0x02, 0x15
                    if (data.length() >= 25 && data[0] == 0x4C && data[1] == 0x00 && data[2] == 0x02 && data[3] == 0x15) {
                        
                        // Tag UUID bytes fra 4 til 19 (de første fire er præfiks)
                        char uuidBuf[37];
                        sprintf(uuidBuf, "%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x",
                                data[4], data[5], data[6], data[7], data[8], data[9], data[10], data[11],
                                data[12], data[13], data[14], data[15], data[16], data[17], data[18], data[19]);
                        for (int j=0; j < targetUUIDs.size(); j++ ) {
                            if (targetUUIDs[j] == std::string(uuidBuf)) {
                                int rssi = device.getRSSI();
                                Serial.printf(">>> TARGET DETECTED! RSSI: %d\n", rssi);
                                
                                payload.push_back({
                                        {"uuid", targetUUIDs[j]},
                                        {"decibel", rssi} 
                                });
                            }
                        }
                    }
                }
            }

            std::string requestBody = payload.dump();

            HTTPClient httpPost;

            String postUrl = serverName + "/data";
            httpPost.begin(postUrl.c_str());

            httpPost.addHeader("Content-Type", "application/json");

            int httpPostCode = httpPost.POST(String(requestBody.c_str()));

            if (httpPostCode > 0) {
                Serial.printf("HTTP response code: %d\n", httpPostCode);
            } else {
                Serial.printf("Error code: %s\n", http.errorToString(httpPostCode).c_str());
            }
            httpPost.end();


            // Stopper med at skanne og cleaner memory
            pBLEScan->stop(); 
            foundDevices.dump();
            pBLEScan->clearResults(); 
            
            Serial.println("Waiting 2 seconds before next scan...");
            delay(2000);
        } else {
            // Er du sikker på at du har skrevet den rigtige IP til serveren og er på samme WiFi?
            Serial.println("Website error code: ");
            Serial.println(httpResponseCode);
        }
    }
    else {
        Serial.println("WiFi Disconnected");
    }
}