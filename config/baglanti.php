<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("DB_HOST", $_ENV["HOST"]);
define("DB_NAME", $_ENV["DB_NAME"]);
define("DB_CHARSET", $_ENV["CHARSET"]);
define("DB_USER", $_ENV["USERNAME"]);
define("DB_PASSWORD", $_ENV["PASSWORD"]);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo $e->getMessage();
}

function query($pdo, $sql, $data = null)
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $stmt;
}
