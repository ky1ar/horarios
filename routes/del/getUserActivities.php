<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = isset($_POST["userId"]) ? intval($_POST["userId"]) : 0;
    $month = isset($_POST["month"]) ? intval($_POST["month"]) : 0;
    $year = isset($_POST["year"]) ? intval($_POST["year"]) : 0;
    $date = sprintf("%04d-%02d", $year, $month); // Formato YYYY-MM

    if ($userId > 0 && $month > 0 && $year > 0) {
        // Consulta para obtener una sola fila
        $sql = "SELECT `desc`, `days`, `services` FROM Activ WHERE id_user = ? AND month_year = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $stmt->bind_result($desc, $days, $services);

        if ($stmt->fetch()) {
            echo json_encode([
                "success" => true,
                "desc" => $desc,
                "days" => $days,
                "services" => $services
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontraron datos"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>
