<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $year = $_POST['year'];

    // Consulta para calcular la sumatoria de mid_time y full_time
    $additionalQuery = "
        SELECT 
            SUM(CASE WHEN s.mid_time = 1 THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN s.full_time = 1 THEN 1 ELSE 0 END) AS total_time
        FROM Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        WHERE s.id_user = ? AND YEAR(c.calendar_date) = ?;
    ";

    $stmt = $conn->prepare($additionalQuery);
    $stmt->bind_param("ii", $userId, $year);
    $stmt->execute();
    $additionalResult = $stmt->get_result();
    $additionalRow = $additionalResult->fetch_assoc();

    // Devolver el total_time calculado
    $totalTime = $additionalRow['total_time'] ?? 0;

    echo json_encode(['total_time' => $totalTime]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}

?>