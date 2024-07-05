<?php
// Establecer encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    $query = "
        SELECT s.stamp
        FROM Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        WHERE s.id_user = ?
        AND c.calendar_date = (
            SELECT calendar_date
            FROM Calendar
            WHERE holiday = 0
            AND DAYOFWEEK(calendar_date) <> 1
            AND calendar_date BETWEEN DATE_SUB(DATE(CONCAT(?, '-', ?, '-01')), INTERVAL 1 MONTH) 
                AND LAST_DAY(DATE_SUB(DATE(CONCAT(?, '-', ?, '-01')), INTERVAL 1 MONTH))
            ORDER BY calendar_date DESC
            LIMIT 1
        );
    ";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die(json_encode(['error' => 'Error de preparación de la consulta: ' . $conn->error]));
    }
    $stmt->bind_param("issss", $userId, $year, $month, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stamp = $row['stamp'];
        $length = strlen($stamp);

        if ($length == 5 || $length > 30) {
            $calculated_time = 'DF';
        } elseif ($length == 10) {
            $start_time = substr($stamp, 0, 5);
            $end_time = substr($stamp, 5, 5);
            $diff = strtotime($end_time) - strtotime($start_time);
            $calculated_time = gmdate('H:i', $diff); 
        } elseif ($length == 20) {
            $start_time = substr($stamp, 0, 5);
            $end_time = substr($stamp, 15, 5);
            $diff = strtotime($end_time) - strtotime($start_time);
            $calculated_time = gmdate('H:i', $diff);
        } elseif ($length == 30) {
            $start_time = substr($stamp, 0, 5);
            $mid_time_start = substr($stamp, 10, 5);
            $mid_time_end = substr($stamp, 20, 5);
            $end_time = substr($stamp, -5);
            $total_diff = strtotime($end_time) - strtotime($start_time);
            $mid_diff = strtotime($mid_time_end) - strtotime($mid_time_start);
            $diff = $total_diff - $mid_diff;
            $calculated_time = gmdate('H:i', $diff);
        } else {
            $calculated_time = 'DF';
        }

        // Construir el array de respuesta
        $response = [
            'stamp' => $stamp,
            'calculated_time' => $calculated_time
        ];

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'No se encontraron resultados.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Faltan parámetros requeridos.']);
}
?>
