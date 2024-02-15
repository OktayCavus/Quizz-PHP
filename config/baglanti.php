<?php
require_once __DIR__ . '/../vendor/autoload.php';

class Database
{
    private $pdo;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $host = $_ENV["HOST"];
        $dbname = $_ENV["DB_NAME"];
        $charset = $_ENV["CHARSET"];
        $username = $_ENV["USERNAME"];
        $password = $_ENV["PASSWORD"];

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=$charset",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function query($sql, $data = null)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $stmt;
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}

// Örnek:
// $db = new Database();
// $pdo = $db->getPdo();
// $result = $db->query("SELECT * FROM table_name");
