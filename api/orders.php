<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null; 

switch ($method) {
    case 'POST':
        handleCreate($pdo);
        break;
    case 'PUT':
        handleUpdate($pdo, $id);
        break;
    case 'DELETE':
        handleDelete($pdo, $id);
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

function handleCreate($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['order_date'], $data['destination_country'], $data['products'])) {
        http_response_code(400);
        die(json_encode(["error" => "Missing required data"]));
    }

    $pdo->beginTransaction();
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        foreach ($data['products'] as $item) {
            $checkStmt->execute([$item['product_id']]);
            if (!$checkStmt->fetch()) {
                throw new Exception("Product with ID " . $item['product_id'] . " does not exist.");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO orders (order_date, destination_country) VALUES (?, ?)");
        $stmt->execute([$data['order_date'], $data['destination_country']]);
        $orderId = $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($data['products'] as $item) {
            if ($item['quantity'] <= 0) throw new Exception("Invalid quantity for product " . $item['product_id']);
            $itemStmt->execute([$orderId, $item['product_id'], $item['quantity']]);
        }

        $pdo->commit();
        http_response_code(201);
        echo json_encode(["message" => "Order created successfully", "order_id" => (int)$orderId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function handleUpdate($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        die(json_encode(["error" => "Order ID is required"]));
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_date = ?, destination_country = ? WHERE id = ?");
        $stmt->execute([$data['order_date'], $data['destination_country'], $id]);

        $check = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) throw new Exception("Order not found.");

        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);
        
        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($data['products'] as $item) {
            $itemStmt->execute([$id, $item['product_id'], $item['quantity']]);
        }

        $pdo->commit();
        echo json_encode(["message" => "Order updated successfully"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function handleDelete($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        die(json_encode(["error" => "Order ID is required"]));
    }

    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(204);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Order not found"]);
    }
}