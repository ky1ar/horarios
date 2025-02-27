<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['week']) && isset($_POST['year']) && isset($_POST['month'])) {
    $userId = $_POST['userId'];
    $week = $_POST['week'];
    $year = $_POST['year'];
    $month = $_POST['month'];

    // Ajuste del primer día del mes
    $firstDayOfMonth = date('N', strtotime("$year-$month-01"));
    if ($firstDayOfMonth == 7) {
        $week++;
    }

    if (!$conn) {
        die(json_encode(["success" => false, "message" => "Error de conexión: " . $conn->connect_error]));
    }

    // Obtener el schedule_type correspondiente al usuario
    $queryProfile = "SELECT schedule_type
                     FROM Profile_History
                     WHERE user_id = ?
                     AND (year < ? OR (year = ? AND month <= ?))
                     ORDER BY year DESC, month DESC
                     LIMIT 1";

    $stmtProfile = $conn->prepare($queryProfile);
    if (!$stmtProfile) {
        die(json_encode(["success" => false, "message" => "Error en la preparación de consulta Profile: " . $conn->error]));
    }

    $stmtProfile->bind_param("isss", $userId, $year, $year, $month);
    if (!$stmtProfile->execute()) {
        die(json_encode(["success" => false, "message" => "Error al ejecutar la consulta Profile: " . $stmtProfile->error]));
    }

    $resultProfile = $stmtProfile->get_result();
    $scheduleType = ($resultProfile->num_rows > 0) ? $resultProfile->fetch_assoc()['schedule_type'] : 0;
    $stmtProfile->close();

    // Consulta principal con el schedule_type
    $query = "SELECT 
        WEEK(c.calendar_date, 1) AS semana,
        u2.id_user,
        ? AS id_profile, 
        (
            SELECT 
                SUM(
                    CASE 
                        WHEN ? = 1 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, 0)
                        WHEN ? = 2 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, IF(DAYNAME(c2.calendar_date) = 'Saturday', 4, 0))
                        WHEN ? = 3 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), 8, 0)
                        ELSE 0
                    END
                ) AS total_valor_dia
            FROM 
                Calendar c2
            WHERE 
                WEEKDAY(c2.calendar_date) BETWEEN 0 AND 5
                AND WEEK(c2.calendar_date, 1) = WEEK(DATE_ADD(CONCAT(?, '-', LPAD(?, 2, '0'), '-01'), INTERVAL (? - 1) WEEK), 1)
                AND YEAR(c2.calendar_date) = ?
                AND MONTH(c2.calendar_date) = ?
                AND c2.holiday = 0
        ) AS acumulado_valor_dia
    FROM 
        Calendar c
    JOIN Users u2 ON u2.id_user = ?
    WHERE 
        WEEKDAY(c.calendar_date) BETWEEN 0 AND 5
        AND WEEK(c.calendar_date, 1) = WEEK(DATE_ADD(CONCAT(?, '-', LPAD(?, 2, '0'), '-01'), INTERVAL (? - 1) WEEK), 1)
        AND YEAR(c.calendar_date) = ?
        AND MONTH(c.calendar_date) = ?
        AND c.holiday = 0
    GROUP BY
        WEEK(c.calendar_date, 1),
        u2.id_user;";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die(json_encode(["success" => false, "message" => "Error en la preparación de consulta principal: " . $conn->error]));
    }

    $stmt->bind_param("iiiisssssisssss", $scheduleType, $scheduleType, $scheduleType, $scheduleType, $year, $month, $week, $year, $month, $userId, $year, $month, $week, $year, $month);
    if (!$stmt->execute()) {
        die(json_encode(["success" => false, "message" => "Error al ejecutar la consulta principal: " . $stmt->error]));
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = array();
        $response['success'] = true;
        $response['data'] = array();
        while ($row = $result->fetch_assoc()) {
            $row['acumulado_valor_dia'] .= ":00";
            $response['data'][] = $row;
        }
        echo json_encode($response);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontraron resultados"]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "No se recibieron los parámetros necesarios en la solicitud POST"]);
}
?>
