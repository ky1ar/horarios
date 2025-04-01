<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sessionUserId = $_POST["sessionUserId"]; // Jefe de área
    $month = $_POST["month"];
    $year = $_POST["year"];
    $selectedUsers = json_decode($_POST["selectedUsers"], true);
    $date = "$year-$month";

    // Determinar la columna según el jefe de área
    $areaColumns = [
        19 => "c_marketing",
        11 => "c_logistica",
        25 => "c_soporte",
        2  => "c_admin",
        20 => "c_gerencia"
    ];

    if (!isset($areaColumns[$sessionUserId])) {
        echo json_encode(["success" => false, "message" => "Usuario sin permisos."]);
        exit();
    }

    $columnToCheck = $areaColumns[$sessionUserId];

    // Convertir selectedUsers en enteros para evitar problemas de comparación
    $selectedUsers = array_map('intval', $selectedUsers);
    $placeholders = implode(',', array_fill(0, count($selectedUsers), '?'));
    $types = str_repeat('i', count($selectedUsers));

    $sql = "SELECT id_user FROM Points WHERE date = ? AND $columnToCheck = 1 AND id_user IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error en la preparación de la consulta: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta"]);
        exit();
    }

    $params = array_merge([$date], $selectedUsers);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[$row["id_user"]] = 1;
    }

    echo json_encode(["success" => true, "data" => $data]);
}
