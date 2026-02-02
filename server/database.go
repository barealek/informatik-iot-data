package main

import (
	"database/sql"
	"log"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

// Vi laver en ny datastruktur som holder vores Database
// På den måde kan vi definere funktioner på den (OOP) som interagerer med SQL
type Database struct {
	db *sql.DB
}

func NewDatabase(connectionString string) Database {
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

type Device struct {
	ID        int       `json:"id"`
	Name      string    `json:"name"`
	UUID      string    `json:"uuid"`
	State     State     `json:"state"`
	CreatedAt time.Time `json:"created_at"`
}

func (db Database) FindAllDevices() (devices []Device) {
	rows, err := db.db.Query("SELECT id, name, uuid, state, created_at FROM devices")
	if err != nil {
		log.Fatal("Fejl under hentning af enheder", err)
	}
	defer rows.Close()

	for rows.Next() {
		var device Device
		err := rows.Scan(&device.ID, &device.Name, &device.UUID, &device.State, &device.CreatedAt)
		if err != nil {
			log.Fatal("Fejl under hentning af enheder", err)
		}
		devices = append(devices, device)
	}

	return
}

func (db Database) UpdateDeviceState(id int, state State) error {
	_, err := db.db.Exec("UPDATE devices SET state = ? WHERE id = ?", state, id)
	if err != nil {
		log.Fatal("Fejl under opdatering af enhed", err)
	}
	return err
}

func (db Database) CreateDevice(name string, uuid string, state State) error {
	_, err := db.db.Exec("INSERT INTO devices (name, uuid, state) VALUES (?, ?, ?)", name, uuid, state)
	if err != nil {
		log.Fatal("Fejl under oprettelse af enhed", err)
	}
	return err
}
