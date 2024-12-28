<?php
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
            AND calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) 
                AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
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

        if ($stamp == '0' || $stamp == null || $stamp == '' || $stamp == 'DF' || $length == 5 || $length > 30) {
            $calculated_time = 'DF';
        } elseif ($length == 10) {
            $start_time = substr($stamp, 0, 5);
            $end_time = substr($stamp, 5, 5);
            $diff = strtotime($end_time) - strtotime($start_time);
            $calculated_time = gmdate('H:i', $diff); 
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

            $calculated_time = gmdate('H:i', $adjusted_diff);
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

            $calculated_time = gmdate('H:i', $adjusted_diff);
        } else {
            $calculated_time = 'DF';
        }

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
