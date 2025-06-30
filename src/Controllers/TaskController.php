<?php

namespace TaskManager\Controllers;

use TaskManager\Models\Task;
use TaskManager\Database\DatabaseManager;
use Exception;

/**
 * Klasa TaskController
 * Kontroler obsługujący operacje na zadaniach
 * Demonstracja: MVC pattern, error handling, clean code
 */
class TaskController
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Pobieranie wszystkich zadań
     */
    public function index(string $status = null): array
    {
        try {
            return Task::getAll($status);
        } catch (Exception $e) {
            throw new Exception("Błąd podczas pobierania zadań: " . $e->getMessage());
        }
    }

    /**
     * Pobieranie zadania po ID
     */
    public function show(int $id): ?Task
    {
        try {
            return Task::findById($id);
        } catch (Exception $e) {
            throw new Exception("Błąd podczas pobierania zadania: " . $e->getMessage());
        }
    }

    /**
     * Tworzenie nowego zadania
     */
    public function create(array $data): Task
    {
        try {
            $task = new Task();
            $task->setTitle($data['title'])
                 ->setDescription($data['description'] ?? null)
                 ->setStatus($data['status'] ?? 'pending')
                 ->setPriority($data['priority'] ?? 'medium')
                 ->setDueDate($data['due_date'] ?? null)
                 ->setCategoryId($data['category_id'] ?? null);

            if (!$task->save()) {
                throw new Exception("Nie udało się zapisać zadania");
            }

            return $task;
        } catch (Exception $e) {
            throw new Exception("Błąd podczas tworzenia zadania: " . $e->getMessage());
        }
    }

    /**
     * Aktualizacja zadania
     */
    public function update(int $id, array $data): Task
    {
        try {
            $task = Task::findById($id);
            if (!$task) {
                throw new Exception("Zadanie nie zostało znalezione");
            }

            if (isset($data['title'])) {
                $task->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $task->setDescription($data['description']);
            }
            if (isset($data['status'])) {
                $task->setStatus($data['status']);
            }
            if (isset($data['priority'])) {
                $task->setPriority($data['priority']);
            }
            if (isset($data['due_date'])) {
                $task->setDueDate($data['due_date']);
            }
            if (isset($data['category_id'])) {
                $task->setCategoryId($data['category_id']);
            }

            if (!$task->save()) {
                throw new Exception("Nie udało się zaktualizować zadania");
            }

            return $task;
        } catch (Exception $e) {
            throw new Exception("Błąd podczas aktualizacji zadania: " . $e->getMessage());
        }
    }

    /**
     * Usuwanie zadania
     */
    public function delete(int $id): bool
    {
        try {
            $task = Task::findById($id);
            if (!$task) {
                throw new Exception("Zadanie nie zostało znalezione");
            }

            return $task->delete();
        } catch (Exception $e) {
            throw new Exception("Błąd podczas usuwania zadania: " . $e->getMessage());
        }
    }

    /**
     * Oznaczanie zadania jako wykonane
     */
    public function markAsCompleted(int $id): Task
    {
        return $this->update($id, ['status' => 'completed']);
    }

    /**
     * Pobieranie kategorii
     */
    public function getCategories(): array
    {
        try {
            $query = "SELECT * FROM categories ORDER BY name";
            return $this->db->select($query);
        } catch (Exception $e) {
            throw new Exception("Błąd podczas pobierania kategorii: " . $e->getMessage());
        }
    }

    /**
     * Pobieranie statystyk zadań
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];
            
            // Liczba zadań według statusu
            $query = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
            $statusStats = $this->db->select($query);
            
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = (int) $stat['count'];
            }
            
            // Liczba zadań według priorytetu
            $query = "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority";
            $priorityStats = $this->db->select($query);
            
            foreach ($priorityStats as $stat) {
                $stats['by_priority'][$stat['priority']] = (int) $stat['count'];
            }
            
            // Zadania przeterminowane
            $query = "SELECT COUNT(*) as count FROM tasks WHERE due_date < CURDATE() AND status != 'completed'";
            $overdueResult = $this->db->select($query);
            $stats['overdue'] = (int) $overdueResult[0]['count'];
            
            // Zadania na dziś
            $query = "SELECT COUNT(*) as count FROM tasks WHERE due_date = CURDATE() AND status != 'completed'";
            $todayResult = $this->db->select($query);
            $stats['due_today'] = (int) $todayResult[0]['count'];

            return $stats;
        } catch (Exception $e) {
            throw new Exception("Błąd podczas pobierania statystyk: " . $e->getMessage());
        }
    }

    /**
     * Wyszukiwanie zadań
     */
    public function search(string $query): array
    {
        try {
            $searchQuery = "SELECT t.*, c.name as category_name, c.color as category_color 
                           FROM tasks t 
                           LEFT JOIN categories c ON t.category_id = c.id 
                           WHERE t.title LIKE :query OR t.description LIKE :query 
                           ORDER BY t.priority DESC, t.due_date ASC";
            
            $searchTerm = '%' . $query . '%';
            $result = $this->db->select($searchQuery, [':query' => $searchTerm]);

            $tasks = [];
            foreach ($result as $taskData) {
                $task = new Task();
                // Użycie refleksji lub metody createFromArray (jeśli publiczna)
                $tasks[] = $taskData; // Uproszczone dla demonstracji
            }
            
            return $tasks;
        } catch (Exception $e) {
            throw new Exception("Błąd podczas wyszukiwania: " . $e->getMessage());
        }
    }
} 