-- ============================================
-- SIAKAD Mini - Seed Data
-- Untuk UTS
-- ============================================

-- -----------------------------------------------------------------------------
-- 1. CREATE TABLES
-- -----------------------------------------------------------------------------

-- Users (untuk autentikasi)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','operator') NOT NULL DEFAULT 'operator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dosen (dengan soft delete)
CREATE TABLE IF NOT EXISTS dosen (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nidn CHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    program_studi ENUM('Teknik Informatika','Sistem Informasi','Teknik Elektro') NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mata Kuliah
CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(12) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    sks TINYINT UNSIGNED NOT NULL CHECK (sks BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dosen_Mata_Kuliah (Many-to-Many)
CREATE TABLE IF NOT EXISTS dosen_matakuliah (
    dosen_id INT UNSIGNED NOT NULL,
    matakuliah_id INT UNSIGNED NOT NULL,
    semester ENUM('Ganjil','Genap') NOT NULL,
    PRIMARY KEY (dosen_id, matakuliah_id, semester),
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    FOREIGN KEY (matakuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log (Audit)
CREATE TABLE IF NOT EXISTS activity_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    aksi VARCHAR(20) NOT NULL,
    entitas VARCHAR(50) NOT NULL,
    entitas_id INT UNSIGNED NULL,
    keterangan VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. INSERT DATA AWAL
-- -----------------------------------------------------------------------------

-- Users (password: password123 / operator123)
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator');

-- Mata Kuliah
INSERT INTO mata_kuliah (kode, nama, sks) VALUES 
('TI101', 'Pengantar Teknologi Informasi', 2),
('TI102', 'Algoritma dan Pemrograman', 3),
('TI103', 'Struktur Data', 3),
('TI104', 'Basis Data', 3),
('TI105', 'Jaringan Komputer', 3),
('TI106', 'Pemrograman Web', 3),
('SI101', 'Analisis Sistem Informasi', 3),
('SI102', 'Manajemen Proyek TI', 2),
('TE101', 'Rangkaian Listrik', 3),
('TE102', 'Elektronika Dasar', 3);

-- Dosen
INSERT INTO dosen (nidn, nama, email, program_studi, status) VALUES 
('001', 'Dr. Ahmad Fauzi, M.Kom', 'ahmad@university.ac.id', 'Teknik Informatika', 'aktif'),
('002', 'Prof. Siti Rahayu, M.Sc', 'siti@university.ac.id', 'Sistem Informasi', 'aktif'),
('003', 'Ir. Budi Santoso, M.T', 'budi@university.ac.id', 'Teknik Elektro', 'aktif'),
('004', 'Dr. Maya Sari, M.Kom', 'maya@university.ac.id', 'Sistem Informasi', 'aktif'),
('005', 'Dr. Rudi Hermawan, M.T', 'rudi@university.ac.id', 'Teknik Informatika', 'aktif');

-- Dosen Mengampu Mata Kuliah
INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id, semester) VALUES 
(1, 1, 'Ganjil'), (1, 2, 'Ganjil'),
(2, 7, 'Ganjil'), (2, 8, 'Ganjil'),
(3, 9, 'Ganjil'), (3, 10, 'Ganjil'),
(4, 7, 'Genap'), (4, 8, 'Genap'),
(5, 3, 'Ganjil'), (5, 4, 'Ganjil');