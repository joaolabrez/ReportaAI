CREATE DATABASE IF NOT EXISTS reportaai;
USE reportaai;

CREATE TABLE chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    protocolo VARCHAR(20) UNIQUE NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    status ENUM('Pendente', 'Em Andamento', 'Resolvido') DEFAULT 'Pendente',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);