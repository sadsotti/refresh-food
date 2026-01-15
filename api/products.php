<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['name']) || !isset($data['co2_saved']) || !is_numeric($data['co2_saved'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid or incomplete data"]);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, co2_saved) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['co2_saved']]);
        
        http_response_code(201);
        echo json_encode(["id" => (int)$pdo->lastInsertId(), "message" => "Product created successfully"]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Product ID is required"]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Product not found"]);
            break;
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, co2_saved = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['co2_saved'], $id]);
        
        echo json_encode(["message" => "Product updated successfully"]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Product ID is required"]);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(204); 
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Product not found"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}