<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sessionUserId = $_POST["sessionUserId"]; // Jefe de área
    $month = $_POST["month"];
    $year = $_POST["year"];
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

    // Obtener solo los usuarios que tienen un 1 en la columna correspondiente
    $sql = "SELECT id_user FROM Points WHERE date = ? AND $columnToCheck = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row["id_user"]] = 1;
    }

    echo json_encode(["success" => true, "data" => $data]);
}
?>
