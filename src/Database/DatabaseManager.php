<?php

namespace TaskManager\Database;

use PDO;
use PDOException;
use Exception;

/**
 * Klasa DatabaseManager
 * Zarządzanie połączeniem z bazą danych
 * Demonstracja: Singleton pattern, error handling, clean code
 */
class DatabaseManager
{
    private static ?DatabaseManager $instance = null;
    private ?PDO $connection = null;
    private array $config;

    /**
     * Konstruktor prywatny - Singleton pattern
     */
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    /**
     * Pobieranie instancji (Singleton)
     */
    public static function getInstance(): DatabaseManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Nawiązywanie połączenia z bazą danych
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

        } catch (PDOException $e) {
            throw new Exception("Błąd połączenia z bazą danych: " . $e->getMessage());
        }
    }

    /**
     * Pobieranie połączenia PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Wykonywanie zapytań SELECT z prepared statements
     */
    public function select(string $query, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Błąd wykonania zapytania SELECT: " . $e->getMessage());
        }
    }

    /**
     * Wykonywanie zapytań INSERT/UPDATE/DELETE
     */
    public function execute(string $query, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Błąd wykonania zapytania: " . $e->getMessage());
        }
    }

    /**
     * Pobieranie ID ostatnio wstawionego rekordu
     */
    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Rozpoczynanie transakcji
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Zatwierdzanie transakcji
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Wycofywanie transakcji
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Zapobieganie klonowaniu (Singleton)
     */
    private function __clone() {}

    /**
     * Zapobieganie deserializacji (Singleton)
     */
    public function __wakeup() {}
} 