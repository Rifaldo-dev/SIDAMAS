-- Database: damas
CREATE DATABASE IF NOT EXISTS damas;
USE damas;

-- Tabel dana_masjid
CREATE TABLE IF NOT EXISTS dana_masjid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waktu_tanggal DATETIME NOT NULL,
    jenis_transaksi ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
    jenis_dana VARCHAR(100) NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default (username: admin, password: admin123)
INSERT INTO admin (username, password, nama) VALUES 
('admin', '$2y$10$.EKiYN5gW60oluGel8w3sujm2QBikST/YUkDQmTOsmczJqHYRE/ba', 'Administrator');

-- Insert sample data
INSERT INTO dana_masjid (waktu_tanggal, jenis_transaksi, jenis_dana, total, keterangan) VALUES
('2025-01-15 10:00:00', 'masuk', 'Infaq Jumat', 500000, 'Infaq Jumat minggu pertama'),
('2025-01-16 14:30:00', 'masuk', 'Donasi', 1000000, 'Donasi dari Bapak Ahmad'),
('2025-01-17 09:00:00', 'masuk', 'Zakat', 2000000, 'Zakat Fitrah'),
('2025-01-18 10:00:00', 'keluar', 'Listrik', 300000, 'Pembayaran listrik bulan Januari'),
('2025-01-19 11:00:00', 'keluar', 'Air', 150000, 'Pembayaran air bulan Januari'),
('2025-01-20 13:00:00', 'keluar', 'Renovasi', 1500000, 'Renovasi kamar mandi masjid');