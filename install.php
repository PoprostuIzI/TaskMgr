<?php
/**
 * Task Manager - Installation Script
 * Demonstracja: Database setup, error handling, user-friendly setup
 */

// Sprawdzenie czy PHP ma wymagane rozszerzenia
$requiredExtensions = ['pdo', 'pdo_mysql'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Instalacja</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        h1 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }
        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #c6f6d5;
        }
        .requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .requirement {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .check { color: #27ae60; }
        .cross { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>🚀 Task Manager - Instalacja</h1>
        
        <div class="requirements">
            <h3>Sprawdzenie wymagań systemowych:</h3>
            
            <div class="requirement">
                <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                    <span class="check">✅</span> PHP <?php echo PHP_VERSION; ?> (wymagane: 7.4+)
                <?php else: ?>
                    <span class="cross">❌</span> PHP <?php echo PHP_VERSION; ?> (wymagane: 7.4+)
                <?php endif; ?>
            </div>
            
            <?php foreach ($requiredExtensions as $extension): ?>
                <div class="requirement">
                    <?php if (extension_loaded($extension)): ?>
                        <span class="check">✅</span> Rozszerzenie <?php echo $extension; ?>
                    <?php else: ?>
                        <span class="cross">❌</span> Rozszerzenie <?php echo $extension; ?> (brakuje)
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($missingExtensions)): ?>
            <div class="alert alert-error">
                <strong>Błąd:</strong> Brakuje wymaganych rozszerzeń PHP: <?php echo implode(', ', $missingExtensions); ?>
            </div>
        <?php endif; ?>

        <?php
        $installationComplete = false;
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($missingExtensions)) {
            try {
                $host = $_POST['host'] ?? 'localhost';
                $port = $_POST['port'] ?? 3306;
                $database = $_POST['database'] ?? 'task_manager';
                $username = $_POST['username'] ?? 'root';
                $password = $_POST['password'] ?? '';

                // Próba połączenia z bazą danych
                $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                // Tworzenie bazy danych jeśli nie istnieje
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$database}`");

                // Wczytanie i wykonanie schematu
                $schemaPath = __DIR__ . '/sql/schema.sql';
                if (file_exists($schemaPath)) {
                    $schema = file_get_contents($schemaPath);
                    // Usunięcie CREATE DATABASE i USE z schema.sql
                    $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
                    $schema = preg_replace('/USE.*?;/s', '', $schema);
                    
                    $pdo->exec($schema);
                }

                // Aktualizacja pliku konfiguracyjnego
                $configPath = __DIR__ . '/config/database.php';
                $configContent = "<?php\n/**\n * Konfiguracja bazy danych\n * Wygenerowano automatycznie przez instalator\n */\n\nreturn [\n";
                $configContent .= "    'host' => '{$host}',\n";
                $configContent .= "    'port' => {$port},\n";
                $configContent .= "    'database' => '{$database}',\n";
                $configContent .= "    'username' => '{$username}',\n";
                $configContent .= "    'password' => '{$password}',\n";
                $configContent .= "    'charset' => 'utf8mb4',\n";
                $configContent .= "    'options' => [\n";
                $configContent .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
                $configContent .= "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
                $configContent .= "        PDO::ATTR_EMULATE_PREPARES => false,\n";
                $configContent .= "        PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"\n";
                $configContent .= "    ]\n];";

                file_put_contents($configPath, $configContent);

                $success = "Instalacja zakończona pomyślnie! Aplikacja jest gotowa do użycia.";
                $installationComplete = true;

            } catch (Exception $e) {
                $error = "Błąd podczas instalacji: " . $e->getMessage();
            }
        }
        ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!$installationComplete && empty($missingExtensions)): ?>
            <form method="POST">
                <h3>Konfiguracja bazy danych:</h3>
                
                <div class="form-group">
                    <label for="host">Host bazy danych:</label>
                    <input type="text" id="host" name="host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="port">Port:</label>
                    <input type="text" id="port" name="port" value="3306" required>
                </div>
                
                <div class="form-group">
                    <label for="database">Nazwa bazy danych:</label>
                    <input type="text" id="database" name="database" value="task_manager" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Nazwa użytkownika:</label>
                    <input type="text" id="username" name="username" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <button type="submit" class="btn">🚀 Zainstaluj Task Manager</button>
            </form>
        <?php elseif ($installationComplete): ?>
            <div style="text-align: center;">
                <a href="public/index.php" class="btn">📋 Przejdź do aplikacji</a>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 0.9em;">
            <p><strong>Task Manager</strong></p>
        </div>
    </div>
</body>
</html> 