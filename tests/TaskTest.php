<?php

namespace TaskManager\Tests;

use PHPUnit\Framework\TestCase;
use TaskManager\Models\Task;
use TaskManager\Database\DatabaseManager;
use Exception;

/**
 * Test jednostkowy dla klasy Task
 * Demonstracja: Unit testing, TDD, clean test code
 */
class TaskTest extends TestCase
{
    private Task $task;

    /**
     * Przygotowanie przed każdym testem
     */
    protected function setUp(): void
    {
        $this->task = new Task();
    }

    /**
     * Test tworzenia nowego zadania
     */
    public function testTaskCreation(): void
    {
        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNull($this->task->getId());
    }

    /**
     * Test ustawiania poprawnego tytułu
     */
    public function testSetValidTitle(): void
    {
        $title = "Test zadania";
        $result = $this->task->setTitle($title);
        
        $this->assertInstanceOf(Task::class, $result); // Fluent interface
        $this->assertEquals($title, $this->task->getTitle());
    }

    /**
     * Test błędu przy pustym tytule
     */
    public function testSetEmptyTitle(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tytuł zadania nie może być pusty");
        
        $this->task->setTitle("");
    }

    /**
     * Test błędu przy zbyt długim tytule
     */
    public function testSetTooLongTitle(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tytuł zadania nie może być dłuższy niż 255 znaków");
        
        $longTitle = str_repeat("a", 256);
        $this->task->setTitle($longTitle);
    }

    /**
     * Test ustawiania opisu
     */
    public function testSetDescription(): void
    {
        $description = "Opis testowego zadania";
        $result = $this->task->setDescription($description);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($description, $this->task->getDescription());
    }

    /**
     * Test ustawiania pustego opisu
     */
    public function testSetNullDescription(): void
    {
        $result = $this->task->setDescription(null);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertNull($this->task->getDescription());
    }

    /**
     * Test ustawiania poprawnego statusu
     */
    public function testSetValidStatus(): void
    {
        $validStatuses = ['pending', 'in_progress', 'completed'];
        
        foreach ($validStatuses as $status) {
            $result = $this->task->setStatus($status);
            $this->assertInstanceOf(Task::class, $result);
            $this->assertEquals($status, $this->task->getStatus());
        }
    }

    /**
     * Test błędu przy niepoprawnym statusie
     */
    public function testSetInvalidStatus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nieprawidłowy status zadania");
        
        $this->task->setStatus("invalid_status");
    }

    /**
     * Test ustawiania poprawnego priorytetu
     */
    public function testSetValidPriority(): void
    {
        $validPriorities = ['low', 'medium', 'high'];
        
        foreach ($validPriorities as $priority) {
            $result = $this->task->setPriority($priority);
            $this->assertInstanceOf(Task::class, $result);
            $this->assertEquals($priority, $this->task->getPriority());
        }
    }

    /**
     * Test błędu przy niepoprawnym priorytecie
     */
    public function testSetInvalidPriority(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nieprawidłowy priorytet zadania");
        
        $this->task->setPriority("invalid_priority");
    }

    /**
     * Test ustawiania poprawnej daty
     */
    public function testSetValidDueDate(): void
    {
        $dateString = "2024-12-31";
        $result = $this->task->setDueDate($dateString);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($dateString, $this->task->getDueDate()->format('Y-m-d'));
    }

    /**
     * Test błędu przy niepoprawnej dacie
     */
    public function testSetInvalidDueDate(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Nieprawidłowy format daty");
        
        $this->task->setDueDate("niepoprawna-data");
    }

    /**
     * Test ustawiania pustej daty
     */
    public function testSetNullDueDate(): void
    {
        $result = $this->task->setDueDate(null);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertNull($this->task->getDueDate());
    }

    /**
     * Test pobierania dozwolonych statusów
     */
    public function testGetAllowedStatuses(): void
    {
        $statuses = Task::getAllowedStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('pending', $statuses);
        $this->assertContains('in_progress', $statuses);
        $this->assertContains('completed', $statuses);
    }

    /**
     * Test pobierania dozwolonych priorytetów
     */
    public function testGetAllowedPriorities(): void
    {
        $priorities = Task::getAllowedPriorities();
        
        $this->assertIsArray($priorities);
        $this->assertContains('low', $priorities);
        $this->assertContains('medium', $priorities);
        $this->assertContains('high', $priorities);
    }

    /**
     * Test konwersji do tablicy
     */
    public function testToArray(): void
    {
        $this->task->setTitle("Test zadania")
                   ->setDescription("Test opis")
                   ->setStatus("pending")
                   ->setPriority("high")
                   ->setDueDate("2024-12-31")
                   ->setCategoryId(1);
        
        $array = $this->task->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals("Test zadania", $array['title']);
        $this->assertEquals("Test opis", $array['description']);
        $this->assertEquals("pending", $array['status']);
        $this->assertEquals("high", $array['priority']);
        $this->assertEquals("2024-12-31", $array['due_date']);
        $this->assertEquals(1, $array['category_id']);
    }

    /**
     * Test fluent interface (method chaining)
     */
    public function testFluentInterface(): void
    {
        $result = $this->task
            ->setTitle("Test zadania")
            ->setDescription("Test opis")
            ->setStatus("pending")
            ->setPriority("high")
            ->setCategoryId(1);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals("Test zadania", $this->task->getTitle());
        $this->assertEquals("Test opis", $this->task->getDescription());
        $this->assertEquals("pending", $this->task->getStatus());
        $this->assertEquals("high", $this->task->getPriority());
        $this->assertEquals(1, $this->task->getCategoryId());
    }

    /**
     * Test domyślnych wartości
     */
    public function testDefaultValues(): void
    {
        $this->task->setTitle("Test zadania");
        
        $this->assertEquals("pending", $this->task->getStatus());
        $this->assertEquals("medium", $this->task->getPriority());
        $this->assertNull($this->task->getDescription());
        $this->assertNull($this->task->getDueDate());
        $this->assertNull($this->task->getCategoryId());
    }
} 