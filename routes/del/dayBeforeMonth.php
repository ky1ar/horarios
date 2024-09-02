<?php
// Establecer encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Consulta para el último día laborable del mes anterior
    $query_last = "
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

    // Consulta para el penúltimo día laborable del mes en curso
    $query_penultimate = "
        SELECT s.stamp
        FROM Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        WHERE s.id_user = ?
        AND c.calendar_date = (
            SELECT calendar_date
            FROM Calendar
            WHERE holiday = 0
            AND DAYOFWEEK(calendar_date) <> 1
            AND calendar_date BETWEEN DATE(CONCAT(?, '-', '-01')) 
                AND LAST_DAY(DATE(CONCAT(?, '-', '-01')))
            ORDER BY calendar_date DESC
            LIMIT 2, 1
        );
    ";

    // Preparar y ejecutar la primera consulta
    $stmt_last = $conn->prepare($query_last);
    if ($stmt_last === false) {
        die(json_encode(['error' => 'Error de preparación de la consulta de último día: ' . $conn->error]));
    }
    $stmt_last->bind_param("issss", $userId, $year, $month, $year, $month);
    $stmt_last->execute();
    $result_last = $stmt_last->get_result();
    $last_day_stamp = null;
    if ($result_last->num_rows > 0) {
        $row_last = $result_last->fetch_assoc();
        $last_day_stamp = $row_last['stamp'];
    }

    // Preparar y ejecutar la segunda consulta
    $stmt_penultimate = $conn->prepare($query_penultimate);
    if ($stmt_penultimate === false) {
        die(json_encode(['error' => 'Error de preparación de la consulta de penúltimo día: ' . $conn->error]));
    }
    $stmt_penultimate->bind_param("sss", $userId, $year, $month);
    $stmt_penultimate->execute();
    $result_penultimate = $stmt_penultimate->get_result();
    $penultimate_day_stamp = null;
    if ($result_penultimate->num_rows > 0) {
        $row_penultimate = $result_penultimate->fetch_assoc();
        $penultimate_day_stamp = $row_penultimate['stamp'];
    }

    function calculate_time($stamp) {
        $length = strlen($stamp);
        if ($stamp == '0' || $stamp == null || $stamp == '' || $stamp == 'DF' || $length == 5 || $length > 30) {
            return 'DF';
        } elseif ($length == 10) {
            $start_time = substr($stamp, 0, 5);
            $end_time = substr($stamp, 5, 5);
            $diff = strtotime($end_time) - strtotime($start_time);
            return gmdate('H:i', $diff);
        } elseif ($length == 20) {
            $start_time = substr($stamp, 0, 5);
            $lunch_start = substr($stamp, 5, 5);
            $lunch_end = substr($stamp, 10, 5);
            $end_time = substr($stamp, 15, 5);

            $start_unix = strtotime($start_time);
            $lunch_start_unix = strtotime($lunch_start);
            $lunch_end_unix = strtotime($lunch_end);
            $end_unix = strtotime($end_time);

            $total_diff = $end_unix - $start_unix;
            $lunch_diff = $lunch_end_unix - $lunch_start_unix;
            $adjusted_diff = $total_diff - $lunch_diff;

            return gmdate('H:i', $adjusted_diff);
        } elseif ($length == 30) {
            $start_time = substr($stamp, 0, 5);
            $lunch_start_1 = substr($stamp, 5, 5);
            $lunch_end_1 = substr($stamp, 10, 5);
            $lunch_start_2 = substr($stamp, 15, 5);
            $lunch_end_2 = substr($stamp, 20, 5);
            $end_time = substr($stamp, 25, 5);

            $start_unix = strtotime($start_time);
            $lunch_start_1_unix = strtotime($lunch_start_1);
            $lunch_end_1_unix = strtotime($lunch_end_1);
            $lunch_start_2_unix = strtotime($lunch_start_2);
            $lunch_end_2_unix = strtotime($lunch_end_2);
            $end_unix = strtotime($end_time);

            $total_diff = $end_unix - $start_unix;
            $lunch_diff_1 = $lunch_end_1_unix - $lunch_start_1_unix;
            $lunch_diff_2 = $lunch_end_2_unix - $lunch_start_2_unix;
            $adjusted_diff = $total_diff - $lunch_diff_1 - $lunch_diff_2;

            return gmdate('H:i', $adjusted_diff);
        } else {
            return 'DF';
        }
    }

    $calculated_last_day = calculate_time($last_day_stamp);
    $calculated_penultimate_day = calculate_time($penultimate_day_stamp);

    $response = [
        'last_day_stamp' => $last_day_stamp,
        'calculated_last_day' => $calculated_last_day,
        'penultimate_day_stamp' => $penultimate_day_stamp,
        'calculated_penultimate_day' => $calculated_penultimate_day
    ];

    echo json_encode($response);

    $stmt_last->close();
    $stmt_penultimate->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Faltan parámetros requeridos.']);
}
?>
