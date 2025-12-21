CREATE DATABASE IF NOT EXISTS mga_encoding_app;
USE mga_encoding_app;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    user_type ENUM('admin', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Information table
CREATE TABLE information (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    tin_number VARCHAR(12) NOT NULL,
    month VARCHAR(20) NOT NULL,
    year YEAR NOT NULL,
    authorized_employee VARCHAR(255),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Vatable Sales table
CREATE TABLE vatable_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Non Vat Sales table
CREATE TABLE non_vat_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Vatable Purchases table
CREATE TABLE vatable_purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Non Vat Purchases table
CREATE TABLE non_vat_purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Vatable Expenses table
CREATE TABLE vatable_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Non Vat Expenses table
CREATE TABLE non_vat_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- CAPEX table
CREATE TABLE capex (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Taxes and Licenses table
CREATE TABLE taxes_licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    info_id INT,
    -- Add your columns here later
    description VARCHAR(255),
    amount DECIMAL(15,2),
    date DATE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (info_id) REFERENCES information(id) ON DELETE CASCADE
);

-- Insert admin account
INSERT INTO users (username, password, user_type) VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'admin');
-- Note: Password for admin is 'password123' - hash this in config.php