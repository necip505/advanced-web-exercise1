<?php
// Task 1: Database backup to TXT then ZIP

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';

// Check if db name is provided
$dbName = isset($_GET['db']) ? $_GET['db'] : (isset($argv[1]) ? $argv[1] : null);

if (!$dbName) {
    die("Please provide a database name via ?db=dbname parameter or CLI argument.\n");
}

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$txtFileName = "backup_{$dbName}_" . date('Ymd_His') . ".txt";
$zipFileName = "backup_{$dbName}_" . date('Ymd_His') . ".zip";

$fileContent = "";

try {
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                // Escape values for SQL
                $escapedValues = array_map(function($value) use ($pdo) {
                    if ($value === null) return 'NULL';
                    return $pdo->quote($value);
                }, $values);

                $columnsStr = implode(", ", array_map(function($col) { return "`$col`"; }, $columns));
                $valuesStr = implode(", ", $escapedValues);

                $fileContent .= "INSERT INTO `$table` ($columnsStr) \nVALUES ($valuesStr);\n\n";
            }
        }
    }

    // Save to .txt file
    file_put_contents($txtFileName, $fileContent);

    // ZIP the .txt file
    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($txtFileName, $txtFileName);
        $zip->close();
        echo "Successfully created backup: <a href='$zipFileName'>$zipFileName</a><br>\n";
        
        // Optional: delete the raw .txt file after zipping
        unlink($txtFileName);
    } else {
        echo "Failed to create ZIP file.\n";
    }

} catch (Exception $e) {
    echo "Error generating backup: " . $e->getMessage() . "\n";
}
?>
