<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];

    $query = "
        SELECT s.stamp
        FROM Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        WHERE s.id_user = ? AND c.calendar_date = '2024-12-30'
        LIMIT 1;
    ";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stamp = $row['stamp'] ?? null;

            if ($stamp) {
                // Procesar el cálculo basado en el formato del `stamp`
                $timePoints = str_split($stamp, 5); // Divide en bloques de 5 caracteres
                if (count($timePoints) >= 4) {
                    // Convertir a timestamps y calcular la diferencia
                    $start = strtotime($timePoints[0]); // Primer tiempo (09:23)
                    $end = strtotime($timePoints[3]); // Último tiempo (19:24)
                    $middle1 = strtotime($timePoints[1]); // Segundo tiempo (13:01)
                    $middle2 = strtotime($timePoints[2]); // Tercer tiempo (14:01)

                    $totalTime = ($end - $start); // Total en segundos (esquinas)
                    $middleTime = ($middle2 - $middle1); // Intermedios en segundos

                    $calculatedDifference = $totalTime - $middleTime; // Diferencia final en segundos

                    // Convertir la diferencia a formato HH:MM
                    $hours = floor($calculatedDifference / 3600);
                    $minutes = floor(($calculatedDifference % 3600) / 60);
                    $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

                    echo json_encode([
                        'success' => true,
                        'stamp' => $stamp,
                        'calculated_time' => $formattedTime
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid stamp format.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Stamp not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No data found for the given user and date.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing userId parameter.']);
}

$conn->close();
?>
