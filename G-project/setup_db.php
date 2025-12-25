<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection to MySQL server
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS bgmi_tournament";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select Database
$conn->select_db("bgmi_tournament");

// Create Tables
$tables = [
    "admin" => "CREATE TABLE IF NOT EXISTS admin (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        password VARCHAR(255) NOT NULL
    )",
    "tournaments" => "CREATE TABLE IF NOT EXISTS tournaments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        date DATETIME,
        prize_pool VARCHAR(50),
        entry_fee VARCHAR(50) DEFAULT 'Free',
        status ENUM('open', 'closed', 'ongoing') DEFAULT 'open',
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "registrations" => "CREATE TABLE IF NOT EXISTS registrations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tournament_id INT(6) UNSIGNED,
        user_id INT(6) UNSIGNED NULL,
        team_name VARCHAR(50) NOT NULL,
        captain_name VARCHAR(50) NOT NULL,
        captain_discord VARCHAR(50),
        player_details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tournament_id) REFERENCES tournaments(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    ),
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        bgmi_id VARCHAR(20),
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table $name created successfully<br>";
    } else {
        echo "Error creating table $name: " . $conn->error . "<br>";
    }
}

// Create default admin user (admin/admin123)
$pass = password_hash("admin123", PASSWORD_DEFAULT);
$checkAdmin = "SELECT * FROM admin WHERE username='admin'";
$result = $conn->query($checkAdmin);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO admin (username, password) VALUES ('admin', '$pass')";
    if ($conn->query($sql) === TRUE) {
        echo "Default admin user created<br>";
    }
}

$conn->close();
?>
