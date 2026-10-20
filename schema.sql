CREATE DATABASE IF NOT EXISTS practica_2fa CHARACTER SET utf8mb4;
USE practica_2fa;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  telefono VARCHAR(20) NULL,
  google_id VARCHAR(255) NULL,
  codigo_verificacion VARCHAR(128) NULL,
  codigo_expiracion DATETIME NULL,
  cuenta_activa TINYINT(1) NOT NULL DEFAULT 0
);