<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../includes/app/db.php';

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];

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
WHERE MONTH(c.calendar_date) = 3
    AND YEAR(c.calendar_date) = 2024;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
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
