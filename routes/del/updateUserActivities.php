<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los datos desde la solicitud AJAX
    $userId = isset($_POST["userId"]) ? intval($_POST["userId"]) : 0;
    $month = isset($_POST["month"]) ? intval($_POST["month"]) : 0;
    $year = isset($_POST["year"]) ? intval($_POST["year"]) : 0;
    $descargas = isset($_POST["descargas"]) ? $_POST["descargas"] : '';
    $dias = isset($_POST["dias"]) ? $_POST["dias"] : '';
    $servicios = isset($_POST["servicios"]) ? $_POST["servicios"] : '';
    $date = sprintf("%04d-%02d", $year, $month); // Formato YYYY-MM

    if ($userId > 0 && $month > 0 && $year > 0) {
        // Consulta para actualizar los datos solo de la fila correspondiente
        $sql = "UPDATE Activ SET `desc` = ?, `days` = ?, `services` = ? WHERE id_user = ? AND month_year = ?";
        $stmt = $conn->prepare($sql);
        
        // Vincular parámetros
        $stmt->bind_param("sssis", $descargas, $dias, $servicios, $userId, $date);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar los datos"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>
