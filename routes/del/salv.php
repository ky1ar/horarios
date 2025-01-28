<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Función para calcular la diferencia de tiempo en formato HH:MM
    function calculateTimeDifference($conn, $userId, $date) {
        $query = "
            SELECT s.stamp
            FROM Schedule s
            JOIN Calendar c ON s.id_calendar = c.id_date
            WHERE s.id_user = ? AND c.calendar_date = ?
            LIMIT 1;
        ";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("is", $userId, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stamp = $row['stamp'] ?? null;

                if ($stamp) {
                    $timePoints = str_split($stamp, 5);
                    if (count($timePoints) >= 4) {
                        $start = strtotime($timePoints[0]);
                        $end = strtotime($timePoints[3]);
                        $middle1 = strtotime($timePoints[1]);
                        $middle2 = strtotime($timePoints[2]);

                        $totalTime = ($end - $start);
                        $middleTime = ($middle2 - $middle1);

                        return $totalTime - $middleTime;
                    }
                }
            }
            $stmt->close();
        }

        return 0; // Retornar 0 si no hay datos
    }

    // Calcular diferencias de tiempo para el 30 y el 31 de diciembre
    $timeDiff30 = calculateTimeDifference($conn, $userId, '2024-12-30');
    $timeDiff31 = calculateTimeDifference($conn, $userId, '2024-12-31');

    // Sumar ambos tiempos
    $totalTime = $timeDiff30 + $timeDiff31;

    // Convertir la diferencia total a formato HH:MM
    $hours = floor($totalTime / 3600);
    $minutes = floor(($totalTime % 3600) / 60);
    $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

    echo json_encode([
        'success' => true,
        'calculated_time' => $formattedTime,
        'details' => [
            'time_diff_30' => gmdate('H:i', $timeDiff30),
            'time_diff_31' => gmdate('H:i', $timeDiff31),
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Missing userId parameter.']);
}

$conn->close();
?>
