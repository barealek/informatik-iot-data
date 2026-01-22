#include <Arduino.h>
#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEScan.h>
#include <BLEAdvertisedDevice.h>

// UUID fra iBeacon beaconen, BeaconScope appen kan emulere det 
String targetUUID = "2a3f54f5-cb20-4abb-b4af-3a87ee3fcb8c"; 

BLEScan* pBLEScan;

void setup() {
    Serial.begin(115200);
    Serial.println("BLE scan starting");

    BLEDevice::init("");
    pBLEScan = BLEDevice::getScan(); 
    pBLEScan->setActiveScan(true);
    pBLEScan->setInterval(100);
    pBLEScan->setWindow(99);  
}

void loop() {
    Serial.println("--- Starting 5-Second Scan ---");
    
    BLEScanResults foundDevices = pBLEScan->start(5, false); 
    
    int count = foundDevices.getCount();
    Serial.printf("Scan complete. Found %d devices.\n", count);

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

                if (targetUUID == String(uuidBuf)) {
                    int rssi = device.getRSSI();
                    Serial.printf(">>> TARGET DETECTED! RSSI: %d\n", rssi);
                }
            }
        }
    }

    // Stopper med at skanne of cleaner memory
    pBLEScan->stop(); 
    foundDevices.dump();
    pBLEScan->clearResults(); 
    
    Serial.println("Waiting 2 seconds before next scan...");
    delay(2000); 
}