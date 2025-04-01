<?php
require_once '../../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'] ?? null;
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    if (!$userId || !$month || !$year) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        exit;
    }

    $date = $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("SELECT c_marketing, c_logistica, c_soporte, c_admin, c_gerencia FROM Points WHERE id_user = ? AND date = ?");
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "data" => array_values($row)]);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontraron registros"]);
    }

    $stmt->close();
    $conn->close();
}
