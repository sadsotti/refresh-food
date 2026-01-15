<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$start   = $_GET['start_date'] ?? '1000-01-01';
$end     = $_GET['end_date'] ?? '9999-12-31';
$country = !empty($_GET['country']) ? $_GET['country'] : null;
$prod_id = !empty($_GET['product_id']) ? (int)$_GET['product_id'] : null;

if (($start !== '1000-01-01' && !strtotime($start)) || ($end !== '9999-12-31' && !strtotime($end))) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid date format. Use YYYY-MM-DD."]);
    exit;
}

try {
    $sql = "SELECT SUM(oi.quantity * p.co2_saved) AS total_co2_saved
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.order_date BETWEEN :start AND :end";

    $params = [
        ':start' => $start,
        ':end'   => $end
    ];

    if ($country) {
        $sql .= " AND o.destination_country = :country";
        $params[':country'] = $country;
    }

    if ($prod_id) {
        $sql .= " AND p.id = :prod_id";
        $params[':prod_id'] = $prod_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();

    echo json_encode([
        "status" => "success",
        "filters" => [
            "period" => ["from" => $start, "to" => $end],
            "country" => $country,
            "product_id" => $prod_id
        ],
        "total_co2_saved" => (float)($result['total_co2_saved'] ?? 0)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error calculating dashboard data"]);
}