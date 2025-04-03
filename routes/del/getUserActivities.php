<?php
require_once '../../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $userId = isset($_GET["userId"]) ? intval($_GET["userId"]) : 0;
    $month = isset($_GET["month"]) ? intval($_GET["month"]) : 0;
    $year = isset($_GET["year"]) ? intval($_GET["year"]) : 0;

    if ($userId > 0 && $month > 0 && $year > 0) {
        $monthYear = sprintf("%04d-%02d", $year, $month); // Formato YYYY-MM

        $sql = "SELECT `desc`, `days`, `services` FROM Activ WHERE id_user = ? AND month_year = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $monthYear]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(["success" => true, "desc" => $result["desc"], "days" => $result["days"], "services" => $result["services"]]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontraron datos"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>
