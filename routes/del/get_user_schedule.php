<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../includes/app/db.php';

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];
    // $month = $_POST['month'];
    $month = 3; // Mes fijo
    $year = 2024; // Año fijo o puedes obtener el año dinámicamente

    // Calcular fechas dinámicamente en PHP
    $firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
    $lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

    // Calcular fecha de inicio ajustada
    $dayOfWeekFirst = date('N', strtotime($firstDayOfMonth));
    $startDate = ($dayOfWeekFirst == 1) ? $firstDayOfMonth : date('Y-m-d', strtotime("$firstDayOfMonth - " . (($dayOfWeekFirst + 6) % 7) . " days"));

    // Calcular fecha de fin ajustada
    $dayOfWeekLast = date('N', strtotime($lastDayOfMonth));
    if ($dayOfWeekLast >= 6) {
        $endDate = $lastDayOfMonth;
    } else {
        $daysToAdd = 6 - $dayOfWeekLast;
        $endDate = date('Y-m-d', strtotime("$lastDayOfMonth + $daysToAdd days"));
    }

    // Consulta para obtener el horario del usuario seleccionado
    $sql = "SELECT c.calendar_date, s.stamp, s.id_schedule,
                CASE DAYOFWEEK(c.calendar_date)
                    WHEN 1 THEN 'Domingo'
                    WHEN 2 THEN 'lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miércoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sábado'
                END AS day_name_espanol,
                DAY(c.calendar_date) AS day_number
            FROM Calendar c
            LEFT JOIN Schedule s ON c.id_date = s.id_calendar
                AND s.id_user = ?
            WHERE c.calendar_date BETWEEN ? AND ?
            ORDER BY c.calendar_date";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedule = array();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row;
    }
    echo json_encode(array('success' => true, 'schedule' => $schedule));
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibió el id del usuario.'));
}
?>
