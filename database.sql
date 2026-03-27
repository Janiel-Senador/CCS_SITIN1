-- CSS SIT-IN MONITORING SYSTEM DATABASE SETUP
-- Date: 2026-03-14

CREATE DATABASE IF NOT EXISTS css_sitin_db;
USE css_sitin_db;

-- 1. Users Table (Handles Students and Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(20) UNIQUE NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    course VARCHAR(100) NOT NULL,
    course_level VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default_profile.png',
    role VARCHAR(20) DEFAULT 'student',
    sessions_remaining INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Reservations Table (For Student Bookings)
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(20) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    lab VARCHAR(50) NOT NULL,
    time_in TIME,
    reservation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Sit-in Records Table (For Active and Historical Sessions)
CREATE TABLE IF NOT EXISTS sitin_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    id_number VARCHAR(20),
    student_name VARCHAR(100),
    purpose VARCHAR(100),
    lab VARCHAR(50),
    time_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_out TIMESTAMP NULL,
    status VARCHAR(20) DEFAULT 'active'
);

-- 5. Default Admin Account
-- ID Number: admin
-- Password: admin
INSERT INTO users (id_number, last_name, first_name, course, course_level, email, address, password, role) 
VALUES ('admin', 'Admin', 'CCS', 'N/A', 'N/A', 'admin@ccs.edu.ph', 'Cebu City', '$2y$10$HrfgLBhjFM7ILZR4tghD9O2fl4KcJdoty.j5ziYKkbtsQxhD9KLJ.', 'admin')
ON DUPLICATE KEY UPDATE role='admin';

-- 6. Sample Announcements
INSERT INTO announcements (title, content, created_at) VALUES 
('Sit-in Guidelines', 'New sit-in guidelines for the upcoming semester have been posted.', '2026-02-11 10:00:00'),
('Website Launch', 'Important Announcement: We are excited to announce the launch of our new website! 🎉 Explore our latest products and services now!', '2024-05-08 09:30:00');
