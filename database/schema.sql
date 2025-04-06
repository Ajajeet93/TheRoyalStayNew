-- Drop tables if they exist (in reverse order of dependencies)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS room_types;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS menu_items;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Room types table
CREATE TABLE IF NOT EXISTS room_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    size VARCHAR(50),
    max_occupancy INT,
    amenities TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
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
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('appetizer', 'main_course', 'dessert', 'beverage') NOT NULL,
    spice_level ENUM('mild', 'medium', 'hot', 'extra_hot') NOT NULL,
    image_url VARCHAR(255),
    is_vegetarian BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample room types
INSERT INTO room_types (name, description, price_per_night, size, max_occupancy, amenities) VALUES
('Standard Room', 'Comfortable room with essential amenities', 750.00, '300 sq ft', 2, '["WiFi","TV","Air Conditioning","Mini Bar"]'),
('Deluxe Room', 'Spacious room with premium amenities', 1000.00, '400 sq ft', 3, '["WiFi","TV","Air Conditioning","Mini Bar","Ocean View","Balcony"]'),
('Suite', 'Luxury suite with separate living area', 1300.00, '600 sq ft', 4, '["WiFi","TV","Air Conditioning","Mini Bar","Ocean View","Balcony","Jacuzzi","Kitchen"]');

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, phone, role, updated_at) VALUES
('Admin', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'admin', NOW());

-- Insert menu items
INSERT INTO menu_items (name, description, price, category, spice_level, image_url, is_vegetarian, updated_at) VALUES
-- Appetizers
('Samosa', 'Crispy pastry filled with spiced potatoes and peas', 4.99, 'appetizer', 'medium', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()),
('Tandoori Chicken Wings', 'Marinated chicken wings cooked in tandoor', 6.99, 'appetizer', 'medium', 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', false, NOW()),

-- Main Course
('Butter Chicken', 'Tender chicken in rich tomato cream sauce', 12.99, 'main_course', 'medium', 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', false, NOW()),
('Paneer Tikka', 'Grilled cottage cheese in spiced yogurt marinade', 11.99, 'main_course', 'medium', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()),
('Lamb Rogan Josh', 'Kashmiri-style lamb curry with aromatic spices', 14.99, 'main_course', 'hot', 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', false, NOW()),
('Dal Makhani', 'Creamy black lentils simmered overnight', 10.99, 'main_course', 'mild', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()),
('Biryani', 'Fragrant basmati rice with aromatic spices', 13.99, 'main_course', 'medium', 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', false, NOW()),

-- Desserts
('Gulab Jamun', 'Sweet milk dumplings in sugar syrup', 3.99, 'dessert', 'mild', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()),
('Mango Kulfi', 'Traditional Indian ice cream with mango', 4.99, 'dessert', 'mild', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()),

-- Beverages
('Masala Chai', 'Spiced Indian tea with milk', 2.99, 'beverage', 'mild', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', true, NOW()); 