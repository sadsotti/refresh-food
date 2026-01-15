<?php
$host = 'localhost';
$db   = 'refresh_food';
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
    PDO::ATTR_EMULATE_PREPARES   => false,                  
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"     
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage()); 
    
    header('Content-Type: application/json', true, 500);
    echo json_encode([
        "status" => "error",
        "message" => "A database connection error has occurred."
    ]);
    exit;
}