-- Task Manager Database Schema
-- Demonstracja umiejętności SQL

-- Tworzenie bazy danych
CREATE DATABASE IF NOT EXISTS task_manager 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE task_manager;

-- Tabela zadań
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela kategorii (demonstracja relacji)
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dodanie kategorii do zadań
ALTER TABLE tasks ADD COLUMN category_id INT,
ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Indeksy dla optymalizacji
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_category ON tasks(category_id);

-- Przykładowe dane
INSERT INTO categories (name, color) VALUES 
('Praca', '#e74c3c'),
('Dom', '#2ecc71'),
('Zakupy', '#f39c12'),
('Zdrowie', '#9b59b6');

INSERT INTO tasks (title, description, status, priority, due_date, category_id) VALUES 
('Ukończenie projektu rekrutacyjnego', 'Stworzenie systemu zarządzania zadaniami w PHP', 'in_progress', 'high', '2024-01-15', 1),
('Zakup produktów spożywczych', 'Mleko, chleb, masło, jajka', 'pending', 'medium', '2024-01-12', 3),
('Wizyta u lekarza', 'Kontrolna wizyta u lekarza rodzinnego', 'pending', 'high', '2024-01-20', 4),
('Sprzątanie mieszkania', 'Generalne porządki w domu', 'pending', 'low', '2024-01-14', 2); 