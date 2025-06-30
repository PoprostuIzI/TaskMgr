<?php
/**
 * Konfiguracja bazy danych
 * Demonstracja dobrych praktyk w konfiguracji
 */

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'task_manager',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
]; 