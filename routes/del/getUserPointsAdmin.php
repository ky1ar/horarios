<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sessionUserId = $_POST["sessionUserId"]; // Jefe de área
    $month = $_POST["month"];
    $year = $_POST["year"];
    $date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);

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

    // Consulta SQL para obtener id_user y el valor de la columna correspondiente
    $sql = "SELECT id_user, $columnToCheck AS valor FROM Points WHERE date = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error en la preparación de la consulta: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Error en la consulta"]);
        exit();
    }

    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[$row["id_user"]] = (int) $row["valor"]; // Asociar por id_user
    }

    echo json_encode(["success" => true, "data" => $data]);
}
