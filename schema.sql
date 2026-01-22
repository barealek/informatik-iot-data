CREATE TABLE IF NOT EXISTS devices (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	uuid VARCHAR(255) NOT NULL,
	state INT NOT NULL, -- 0: væk, 1: nær, 2: tæt
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS changes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	device_id INT NOT NULL,
	from_state INT NOT NULL, -- 0: væk, 1: nær, 2: tæt
	to_state INT NOT NULL, -- 0: væk, 1: nær, 2: tæt
	timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (device_id) REFERENCES devices(id)
);
