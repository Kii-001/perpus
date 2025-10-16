-- Buat database
CREATE DATABASE perpustakaan_itbi;
USE perpustakaan_itbi;

-- Tabel admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel mahasiswa/user
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    angkatan YEAR NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel kategori buku
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Tabel buku
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    isbn VARCHAR(20),
    kategori_id INT,
    stok INT NOT NULL DEFAULT 0,
    file_pdf VARCHAR(255),
    sampul VARCHAR(255),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Tabel peminjaman
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    tanggal_jatuh_tempo DATE NOT NULL,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
    denda DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    FOREIGN KEY (buku_id) REFERENCES buku(id)
);

-- Tabel denda
CREATE TABLE denda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    jumlah_denda DECIMAL(10,2) NOT NULL,
    status_bayar ENUM('belum', 'lunas') DEFAULT 'belum',
    tanggal_bayar DATE,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id)
);

-- Insert admin default
INSERT INTO admin (username, password, nama, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@itbi.ac.id');

-- Insert beberapa kategori
INSERT INTO kategori (nama_kategori, deskripsi) VALUES 
('Teknologi', 'Buku tentang teknologi dan informatika'),
('Bisnis', 'Buku tentang manajemen dan bisnis'),
('Sains', 'Buku tentang ilmu pengetahuan'),
('Sastra', 'Buku sastra dan fiksi');


-- Tambahkan beberapa data sample untuk testing
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori_id, stok, deskripsi) VALUES
('Pemrograman Web dengan PHP', 'Budi Raharjo', 'Informatika', 2023, '978-623-123-456-1', 1, 5, 'Buku panduan lengkap pemrograman web menggunakan PHP'),
('Manajemen Bisnis Modern', 'Sari Dewi', 'Erlangga', 2022, '978-623-123-456-2', 2, 3, 'Konsep dan praktik manajemen bisnis di era modern'),
('Data Science Fundamentals', 'Ahmad Fauzi', 'PT. Gramedia', 2023, '978-623-123-456-3', 1, 4, 'Dasar-dasar data science dan machine learning'),
('Kewirausahaan Digital', 'Rina Melati', 'Andi Offset', 2022, '978-623-123-456-4', 2, 6, 'Strategi berwirausaha di era digital'),
('Algoritma dan Struktur Data', 'Dian Pratama', 'Informatika', 2023, '978-623-123-456-5', 1, 2, 'Konsep fundamental algoritma dan struktur data');

-- Insert sample peminjaman
INSERT INTO peminjaman (mahasiswa_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 'terlambat'),
(1, 2, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'dipinjam'),
(2, 3, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 'dikembalikan');

-- Insert sample denda
INSERT INTO denda (peminjaman_id, jumlah_denda, status_bayar) VALUES
(1, 25000, 'belum');