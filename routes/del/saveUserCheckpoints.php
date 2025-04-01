<?php
require_once '../../includes/app/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sessionUserId = $_POST["sessionUserId"];
    $month = $_POST["month"];
    $year = $_POST["year"];
    $selectedUsers = json_decode($_POST["selectedUsers"], true);

    // Determinar la columna según el jefe de área
    $areaColumns = [
        19 => "c_marketing",
        11 => "c_logistica",
        25 => "c_soporte",
        2  => "c_admin",
        20 => "c_gerencia"
    ];

    if (!isset($areaColumns[$sessionUserId])) {
        echo json_encode(["success" => false, "message" => "Usuario sin permisos para modificar."]);
        exit();
    }

    $columnToUpdate = $areaColumns[$sessionUserId]; // Columna correspondiente

    foreach ($selectedUsers as $userId) {
        $date = "$year-$month";

        // Verificar si ya existe el registro para ese usuario y fecha
        $sql = "SELECT id_point FROM Points WHERE id_user = ? AND date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si existe, actualizar el valor de la columna correspondiente
            $sqlUpdate = "UPDATE Points SET $columnToUpdate = 1 WHERE id_user = ? AND date = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("is", $userId, $date);
            $stmtUpdate->execute();
        } else {
            // Si no existe, insertar un nuevo registro con la columna correspondiente en 1
            $sqlInsert = "INSERT INTO Points (id_user, date, $columnToUpdate) VALUES (?, ?, 1)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("is", $userId, $date);
            $stmtInsert->execute();
        }
    }

    echo json_encode(["success" => true, "message" => "Datos guardados correctamente."]);
}
?>
