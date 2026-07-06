CREATE DATABASE IF NOT EXISTS ukm_schedule;
USE ukm_schedule;

CREATE TABLE admin (
    id_admin INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE pengguna (
    id_pengguna INT(11) AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE jenis_jadwal (
    id_jenis_jadwal INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_jenis VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE detail_jadwal (
    id_detail_jadwal INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT(11) NOT NULL,
    id_jenis_jadwal INT(11) NOT NULL,
    id_admin INT(11) NOT NULL,
    tanggal_tugas DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    status_tugas ENUM('Belum Selesai','Selesai') DEFAULT 'Belum Selesai',
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_jenis_jadwal) REFERENCES jenis_jadwal(id_jenis_jadwal),
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
);

-- BARU (benar - sudah di-hash bcrypt, password: password)
INSERT INTO admin (username, nama_lengkap, password)
VALUES ('admin', 'Administrator', 'Admin99');

-- Seed: jenis jadwal
INSERT INTO jenis_jadwal (nama_jenis) VALUES ('Piket'), ('Belanja'), ('Rapat');