<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sessionUserId = $_POST["sessionUserId"];
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

    $columnToModify = $areaColumns[$sessionUserId];

    // Si no hay "updates", solo devuelve los datos
    if (empty($_POST["updates"])) {
        $sql = "SELECT id_user, $columnToModify AS valor FROM Points WHERE date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row["id_user"]] = (int) $row["valor"];
        }

        echo json_encode(["success" => true, "data" => $data]);
        exit();
    }

    // Si hay "updates", insertar o actualizar
    $updates = json_decode($_POST["updates"], true);
    foreach ($updates as $update) {
        $id_user = $update["id_user"];
        $value = $update["value"];

        // Verificar si la fila ya existe
        $sqlCheck = "SELECT id_user FROM Points WHERE date = ? AND id_user = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $date, $id_user);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows == 0) {
            // Insertar si no existe
            $sqlInsert = "INSERT INTO Points (date, id_user, $columnToModify) VALUES (?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("sii", $date, $id_user, $value);
            $stmtInsert->execute();
        } else {
            // Actualizar si ya existe
            $sqlUpdate = "UPDATE Points SET $columnToModify = ? WHERE date = ? AND id_user = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("isi", $value, $date, $id_user);
            $stmtUpdate->execute();
        }
    }

    echo json_encode(["success" => true, "message" => "Datos actualizados correctamente."]);
}
