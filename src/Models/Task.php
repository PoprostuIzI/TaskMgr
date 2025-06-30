<?php

namespace TaskManager\Models;

use TaskManager\Database\DatabaseManager;
use DateTime;
use Exception;

/**
 * Klasa Task - Model zadania
 * Demonstracja: PHP OOP, enkapsulacja, walidacja danych, CRUD operations
 */
class Task
{
    private ?int $id = null;
    private string $title;
    private ?string $description = null;
    private string $status = 'pending';
    private string $priority = 'medium';
    private ?DateTime $dueDate = null;
    private ?int $categoryId = null;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;

    // Dozwolone wartości dla walidacji
    private const ALLOWED_STATUSES = ['pending', 'in_progress', 'completed'];
    private const ALLOWED_PRIORITIES = ['low', 'medium', 'high'];

    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Gettery - demonstracja enkapsulacji
     */
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getPriority(): string { return $this->priority; }
    public function getDueDate(): ?DateTime { return $this->dueDate; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }

    /**
     * Settery z walidacją
     */
    public function setTitle(string $title): self
    {
        if (empty(trim($title))) {
            throw new Exception("Tytuł zadania nie może być pusty");
        }
        if (strlen($title) > 255) {
            throw new Exception("Tytuł zadania nie może być dłuższy niż 255 znaków");
        }
        $this->title = trim($title);
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description ? trim($description) : null;
        return $this;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::ALLOWED_STATUSES)) {
            throw new Exception("Nieprawidłowy status zadania");
        }
        $this->status = $status;
        return $this;
    }

    public function setPriority(string $priority): self
    {
        if (!in_array($priority, self::ALLOWED_PRIORITIES)) {
            throw new Exception("Nieprawidłowy priorytet zadania");
        }
        $this->priority = $priority;
        return $this;
    }

    public function setDueDate(?string $dueDate): self
    {
        if ($dueDate) {
            $date = DateTime::createFromFormat('Y-m-d', $dueDate);
            if (!$date) {
                throw new Exception("Nieprawidłowy format daty (wymagany: YYYY-MM-DD)");
            }
            $this->dueDate = $date;
        } else {
            $this->dueDate = null;
        }
        return $this;
    }

    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * Zapisywanie zadania do bazy danych
     */
    public function save(): bool
    {
        if ($this->id === null) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    /**
     * Tworzenie nowego zadania
     */
    private function create(): bool
    {
        $query = "INSERT INTO tasks (title, description, status, priority, due_date, category_id) 
                  VALUES (:title, :description, :status, :priority, :due_date, :category_id)";
        
        $params = [
            ':title' => $this->title,
            ':description' => $this->description,
            ':status' => $this->status,
            ':priority' => $this->priority,
            ':due_date' => $this->dueDate ? $this->dueDate->format('Y-m-d') : null,
            ':category_id' => $this->categoryId
        ];

        if ($this->db->execute($query, $params)) {
            $this->id = (int) $this->db->getLastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Aktualizacja istniejącego zadania
     */
    private function update(): bool
    {
        $query = "UPDATE tasks 
                  SET title = :title, description = :description, status = :status, 
                      priority = :priority, due_date = :due_date, category_id = :category_id,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $params = [
            ':id' => $this->id,
            ':title' => $this->title,
            ':description' => $this->description,
            ':status' => $this->status,
            ':priority' => $this->priority,
            ':due_date' => $this->dueDate ? $this->dueDate->format('Y-m-d') : null,
            ':category_id' => $this->categoryId
        ];

        return $this->db->execute($query, $params);
    }

    /**
     * Wczytywanie zadania z bazy danych po ID
     */
    public static function findById(int $id): ?Task
    {
        $db = DatabaseManager::getInstance();
        $query = "SELECT * FROM tasks WHERE id = :id";
        $result = $db->select($query, [':id' => $id]);

        if (empty($result)) {
            return null;
        }

        return self::createFromArray($result[0]);
    }

    /**
     * Pobieranie wszystkich zadań
     */
    public static function getAll(string $status = null): array
    {
        $db = DatabaseManager::getInstance();
        
        if ($status && in_array($status, self::ALLOWED_STATUSES)) {
            $query = "SELECT t.*, c.name as category_name, c.color as category_color 
                      FROM tasks t 
                      LEFT JOIN categories c ON t.category_id = c.id 
                      WHERE t.status = :status 
                      ORDER BY t.priority DESC, t.due_date ASC";
            $result = $db->select($query, [':status' => $status]);
        } else {
            $query = "SELECT t.*, c.name as category_name, c.color as category_color 
                      FROM tasks t 
                      LEFT JOIN categories c ON t.category_id = c.id 
                      ORDER BY t.priority DESC, t.due_date ASC";
            $result = $db->select($query);
        }

        $tasks = [];
        foreach ($result as $taskData) {
            $tasks[] = self::createFromArray($taskData);
        }
        return $tasks;
    }

    /**
     * Usuwanie zadania
     */
    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $query = "DELETE FROM tasks WHERE id = :id";
        return $this->db->execute($query, [':id' => $this->id]);
    }

    /**
     * Tworzenie obiektu Task z tablicy danych
     */
    private static function createFromArray(array $data): Task
    {
        $task = new self();
        $task->id = (int) $data['id'];
        $task->title = $data['title'];
        $task->description = $data['description'];
        $task->status = $data['status'];
        $task->priority = $data['priority'];
        $task->categoryId = $data['category_id'] ? (int) $data['category_id'] : null;
        
        if ($data['due_date']) {
            $task->dueDate = new DateTime($data['due_date']);
        }
        
        if ($data['created_at']) {
            $task->createdAt = new DateTime($data['created_at']);
        }
        
        if ($data['updated_at']) {
            $task->updatedAt = new DateTime($data['updated_at']);
        }

        return $task;
    }

    /**
     * Konwersja do tablicy (dla JSON API)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->dueDate ? $this->dueDate->format('Y-m-d') : null,
            'category_id' => $this->categoryId,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null
        ];
    }

    /**
     * Pobieranie dozwolonych statusów
     */
    public static function getAllowedStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    /**
     * Pobieranie dozwolonych priorytetów
     */
    public static function getAllowedPriorities(): array
    {
        return self::ALLOWED_PRIORITIES;
    }
} 