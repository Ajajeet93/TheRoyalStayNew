<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

try {
    // Drop database if it exists
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    echo "Database dropped successfully\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE " . DB_NAME);
    echo "Database created successfully\n";
    
    // Connect to the database
    $db = new Database();
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS room_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price_per_night DECIMAL(10,2) NOT NULL,
            size VARCHAR(50),
            max_occupancy INT,
            amenities TEXT,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_type_id INT NOT NULL,
            room_number VARCHAR(10) NOT NULL UNIQUE,
            floor INT NOT NULL,
            status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (room_type_id) REFERENCES room_types(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            room_type_id INT NOT NULL,
            check_in DATE NOT NULL,
            check_out DATE NOT NULL,
            guests INT NOT NULL,
            special_requests TEXT,
            guest_name VARCHAR(100) NOT NULL,
            guest_email VARCHAR(100) NOT NULL,
            guest_phone VARCHAR(20) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            FOREIGN KEY (room_type_id) REFERENCES room_types(id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )"
    ];
    
    foreach ($tables as $sql) {
        $db->query($sql);
        echo "Table created successfully\n";
    }
    
    // Create admin user if not exists
    $admin_email = 'admin@hotel.com';
    $admin_exists = $db->query("SELECT id FROM users WHERE email = ?", [$admin_email])->fetch(PDO::FETCH_ASSOC);

    if (!$admin_exists) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO users (name, email, password, phone, is_admin) VALUES (?, ?, ?, ?, 1)",
            ['Admin', $admin_email, $admin_password, '1234567890']
        );
    }

    // Insert sample data
    $sample_data = [
        // Room Types
        "INSERT INTO room_types (name, description, price_per_night, size, max_occupancy, amenities, image_url, updated_at) VALUES 
        ('Standard Room', 'Comfortable room with essential amenities', 7500.00, '300 sq ft', 2, '[\"WiFi\",\"TV\",\"Air Conditioning\",\"Mini Bar\"]', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', NOW()),
        ('Deluxe Room', 'Spacious room with premium amenities', 11250.00, '400 sq ft', 3, '[\"WiFi\",\"TV\",\"Air Conditioning\",\"Mini Bar\",\"Ocean View\",\"Balcony\"]', 'https://images.unsplash.com/photo-1591088398332-8a7791972843?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', NOW()),
        ('Suite', 'Luxury suite with separate living area', 18750.00, '600 sq ft', 4, '[\"WiFi\",\"TV\",\"Air Conditioning\",\"Mini Bar\",\"Ocean View\",\"Balcony\",\"Living Room\",\"Kitchen\"]', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', NOW())"
    ];
    
    foreach ($sample_data as $sql) {
        $db->query($sql);
        echo "Sample data inserted successfully\n";
    }
    
    echo "\nSetup completed successfully!\n";
    
} catch(PDOException $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
} 