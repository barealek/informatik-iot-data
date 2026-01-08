package main

import (
	"encoding/json"
	"log"
	"net/http"
)

func main() {
	// Vi opretter en ny database forbindelse
	connectionString := "root:strong(!)Pass@tcp(db:3306)/iot_opsamling"
	var db = NyDatabase(connectionString)
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

	router.HandleFunc("GET /mac-adresser", func(w http.ResponseWriter, r *http.Request) {
		alle_adresser := db.FindDevicesForMacs()

		json.NewEncoder(w).Encode(alle_adresser) // ["abc-255", "abc-258",]
	})

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
	}()

	select {}
}
