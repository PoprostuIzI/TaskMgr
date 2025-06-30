<?php
/**
 * Task Manager - Główny plik aplikacji
 * Demonstracja: Clean code, error handling, user interface
 */

// Autoloader (w rzeczywistym projekcie użyłbym Composer)
spl_autoload_register(function ($class) {
    $prefix = 'TaskManager\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use TaskManager\Controllers\TaskController;
use TaskManager\Models\Task;

// Obsługa błędów
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$controller = new TaskController();
$error = null;
$success = null;

// Obsługa akcji POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $task = $controller->create($_POST);
                $success = "Zadanie zostało dodane pomyślnie!";
                break;
                
            case 'update':
                $task = $controller->update((int)$_POST['id'], $_POST);
                $success = "Zadanie zostało zaktualizowane!";
                break;
                
            case 'delete':
                $controller->delete((int)$_POST['id']);
                $success = "Zadanie zostało usunięte!";
                break;
                
            case 'complete':
                $controller->markAsCompleted((int)$_POST['id']);
                $success = "Zadanie oznaczono jako wykonane!";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Pobieranie danych
try {
    $statusFilter = $_GET['status'] ?? null;
    $tasks = $controller->index($statusFilter);
    $categories = $controller->getCategories();
    $statistics = $controller->getStatistics();
    $editTask = null;
    
    if (isset($_GET['edit'])) {
        $editTask = $controller->show((int)$_GET['edit']);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $tasks = [];
    $categories = [];
    $statistics = [];
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - System Zarządzania Zadaniami</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><i class="fas fa-tasks"></i> Task Manager</h1>
            <p class="subtitle">System Zarządzania Zadaniami</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Statystyki -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $statistics['by_status']['pending'] ?? 0; ?></h3>
                    <p>Oczekujące</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon in-progress">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $statistics['by_status']['in_progress'] ?? 0; ?></h3>
                    <p>W trakcie</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $statistics['by_status']['completed'] ?? 0; ?></h3>
                    <p>Ukończone</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon overdue">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $statistics['overdue'] ?? 0; ?></h3>
                    <p>Przeterminowane</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <!-- Formularz dodawania/edycji zadań -->
            <div class="form-section">
                <h2><?php echo $editTask ? 'Edytuj zadanie' : 'Dodaj nowe zadanie'; ?></h2>
                <form method="POST" class="task-form">
                    <input type="hidden" name="action" value="<?php echo $editTask ? 'update' : 'create'; ?>">
                    <?php if ($editTask): ?>
                        <input type="hidden" name="id" value="<?php echo $editTask->getId(); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Tytuł zadania *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo $editTask ? htmlspecialchars($editTask->getTitle()) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Opis</label>
                        <textarea id="description" name="description" rows="3"><?php echo $editTask ? htmlspecialchars($editTask->getDescription() ?? '') : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <?php foreach (Task::getAllowedStatuses() as $status): ?>
                                    <option value="<?php echo $status; ?>" 
                                            <?php echo ($editTask && $editTask->getStatus() === $status) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">Priorytet</label>
                            <select id="priority" name="priority">
                                <?php foreach (Task::getAllowedPriorities() as $priority): ?>
                                    <option value="<?php echo $priority; ?>"
                                            <?php echo ($editTask && $editTask->getPriority() === $priority) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($priority); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">Termin wykonania</label>
                            <input type="date" id="due_date" name="due_date"
                                   value="<?php echo $editTask && $editTask->getDueDate() ? $editTask->getDueDate()->format('Y-m-d') : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Kategoria</label>
                            <select id="category_id" name="category_id">
                                <option value="">Wybierz kategorię</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo ($editTask && $editTask->getCategoryId() == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $editTask ? 'Zaktualizuj zadanie' : 'Dodaj zadanie'; ?>
                        </button>
                        <?php if ($editTask): ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Anuluj
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Filtry -->
            <div class="filters-section">
                <h3>Filtry</h3>
                <div class="filter-buttons">
                    <a href="index.php" class="btn <?php echo !$statusFilter ? 'btn-active' : 'btn-outline'; ?>">
                        Wszystkie
                    </a>
                    <a href="?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-active' : 'btn-outline'; ?>">
                        Oczekujące
                    </a>
                    <a href="?status=in_progress" class="btn <?php echo $statusFilter === 'in_progress' ? 'btn-active' : 'btn-outline'; ?>">
                        W trakcie
                    </a>
                    <a href="?status=completed" class="btn <?php echo $statusFilter === 'completed' ? 'btn-active' : 'btn-outline'; ?>">
                        Ukończone
                    </a>
                </div>
            </div>

            <!-- Lista zadań -->
            <div class="tasks-section">
                <h2>Lista zadań (<?php echo count($tasks); ?>)</h2>
                
                <?php if (empty($tasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox fa-3x"></i>
                        <h3>Brak zadań</h3>
                        <p>Dodaj pierwsze zadanie używając formularza powyżej.</p>
                    </div>
                <?php else: ?>
                    <div class="tasks-grid">
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-card <?php echo $task->getStatus(); ?> priority-<?php echo $task->getPriority(); ?>">
                                <div class="task-header">
                                    <h3><?php echo htmlspecialchars($task->getTitle()); ?></h3>
                                    <div class="task-actions">
                                        <a href="?edit=<?php echo $task->getId(); ?>" class="btn-icon" title="Edytuj">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($task->getStatus() !== 'completed'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="complete">
                                                <input type="hidden" name="id" value="<?php echo $task->getId(); ?>">
                                                <button type="submit" class="btn-icon" title="Oznacz jako wykonane">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $task->getId(); ?>">
                                            <button type="submit" class="btn-icon btn-danger" title="Usuń">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <?php if ($task->getDescription()): ?>
                                    <p class="task-description"><?php echo htmlspecialchars($task->getDescription()); ?></p>
                                <?php endif; ?>
                                
                                <div class="task-meta">
                                    <span class="status-badge status-<?php echo $task->getStatus(); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $task->getStatus())); ?>
                                    </span>
                                    <span class="priority-badge priority-<?php echo $task->getPriority(); ?>">
                                        <?php echo ucfirst($task->getPriority()); ?>
                                    </span>
                                    <?php if ($task->getDueDate()): ?>
                                        <span class="due-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo $task->getDueDate()->format('d.m.Y'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2025 Task Manager</p>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html> 