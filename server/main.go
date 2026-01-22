package main

import (
	"encoding/json"
	"log"
	"net/http"
)

var db Database

type State int

const (
	StateGone = iota
	StateNear
	StateClose
)

func DecibelToState(dB int) State {
	if dB > -80 {
		return StateClose
	}
	return StateNear
}

func main() {
	// Vi opretter en ny database forbindelse
	connectionString := "root:strong(!)Pass@tcp(db:3306)/iot_opsamling?parseTime=true"
	db = NewDatabase(connectionString)
	// Lukker den lige inden main funktionen afsluttes, altså når programmet stopper
	defer db.Close()

	// Vi opretter en ny router, til at mappe http-requests til funktioner
	router := http.NewServeMux()

	// Registrerer en funktion til GET /health endpointet.
	// Funktionen får w http.ResponseWriter og r *http.Request
	// De to variabler bruges til hhv. at skrive et response og udtrække information fra requesten
	router.HandleFunc("GET /health", func(w http.ResponseWriter, r *http.Request) {
		// Vi skriver bare stringen 'Healthy'
		w.Write([]byte("Healthy"))
	})

	router.HandleFunc("POST /data", handlePostData)
	router.HandleFunc("GET /devices", handleGetAllDevices)

	server := http.Server{
		Addr:    ":8080",
		Handler: router,
	}

	go func() {
		log.Println("Starter server")
		err := server.ListenAndServe()
		if err != nil && err != http.ErrServerClosed {
			log.Fatal(err)
		}
		log.Println("Server stopped")
	}()

	// Vent for evigt
	select {}
}

func handlePostData(w http.ResponseWriter, r *http.Request) {

	type deviceData struct {
		UUID    string `json:"uuid"`
		Decibel int    `json:"decibel"`
	}

	// Vi laver en liste som skal gemme vores data
	requestBody := []deviceData{}

	// Decode requestens body ind i vores requestBody variabel
	if err := json.NewDecoder(r.Body).Decode(&requestBody); err != nil {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte("Invalid request body"))
		return
	}

	deviceDecibelMap := make(map[string]int)
	for _, req := range requestBody {
		deviceDecibelMap[req.UUID] = req.Decibel
	}

	devices := db.FindAllDevices()

	for _, device := range devices {
		if decibel, ok := deviceDecibelMap[device.UUID]; ok {
			state := DecibelToState(decibel)
			db.UpdateDeviceState(device.ID, state)
		} else {
			db.UpdateDeviceState(device.ID, StateGone)
		}
	}

	w.WriteHeader(http.StatusOK)
	w.Write(nil)
}

func handleGetAllDevices(w http.ResponseWriter, r *http.Request) {
	devices := db.FindAllDevices()
	json.NewEncoder(w).Encode(devices)
}
