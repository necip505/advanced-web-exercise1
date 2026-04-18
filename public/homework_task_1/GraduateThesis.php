<?php

require_once 'iRadio.php';

class GraduateThesis implements iRadio {
    public $work_name;
    public $work_text;
    public $work_link;
    public $identification_number;

    private $pdo;

    public function __construct() {
        // Initialize database connection
        // Set up the database and table if they don't exist
        try {
            // First connect without specific DB to create it if needed
            $initPdo = new PDO('mysql:host=localhost', 'root', '');
            $initPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $initPdo->exec("CREATE DATABASE IF NOT EXISTS thesis");
            
            // Now connect to the thesis database
            $this->pdo = new PDO('mysql:host=localhost;dbname=thesis', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create table
            $sql = "CREATE TABLE IF NOT EXISTS graduate_theses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                work_name VARCHAR(255) NOT NULL,
                work_text TEXT NOT NULL,
                work_link VARCHAR(255) NOT NULL,
                identification_number VARCHAR(100) NOT NULL
            )";
            $this->pdo->exec($sql);
            
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function create($work_name, $work_text, $work_link, $identification_number) {
        $this->work_name = $work_name;
        $this->work_text = $work_text;
        $this->work_link = $work_link;
        $this->identification_number = $identification_number;
    }

    public function save() {
        if (!$this->work_name) return; // Prevent empty saves
        
        $stmt = $this->pdo->prepare("INSERT INTO graduate_theses (work_name, work_text, work_link, identification_number) VALUES (:work_name, :work_text, :work_link, :identification_number)");
        $stmt->execute([
            ':work_name' => $this->work_name,
            ':work_text' => $this->work_text,
            ':work_link' => $this->work_link,
            ':identification_number' => $this->identification_number
        ]);
        
        return $this->pdo->lastInsertId();
    }

    public function read() {
        $stmt = $this->pdo->query("SELECT * FROM graduate_theses");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
