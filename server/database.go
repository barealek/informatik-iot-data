package main

import (
	"database/sql"
	"log"

	_ "github.com/go-sql-driver/mysql"
)

// Vi laver en ny datastruktur som holder vores Database
// På den måde kan vi definere funktioner på den (OOP) som interagerer med SQL
type Database struct {
	db *sql.DB
}

func NyDatabase(connectionString string) Database {
	database, err := sql.Open("mysql", connectionString)
	if err != nil {
		log.Fatal("Fejl under forbindelse til server", err)
		// Exit ud hvis DB-forbindelse ikke kan oprettes
	}

	return Database{
		db: database,
	}
}

func (db Database) Close() error {
	// Close skal bare lukke databasens underliggende connection
	return db.db.Close()
}

type Device struct{}

func (db Database) FindDevicesForMacs(macs []string) (devices []Device) {
	err := db.db.QueryRow("SELECT * FROM devices WHERE mac IN (?)", macs).Scan(&devices)
	if err != nil {
		log.Fatal("Fejl under hentning af enheder", err)
	}
	return
}

func (db Database) FindAllDevices() (devices []Device) {
	rows, err := db.db.Query("SELECT * FROM devices")
	if err != nil {
		log.Fatal("Fejl under hentning af enheder", err)
	}
	defer rows.Close()

	for rows.Next() {
		var device Device
		err := rows.Scan(&device)
		if err != nil {
			log.Fatal("Fejl under hentning af enheder", err)
		}
		devices = append(devices, device)
	}

	return
}
