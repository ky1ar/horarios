<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['week']) && isset($_POST['year']) && isset($_POST['month'])) {
    $userId = $_POST['userId'];
    $week = $_POST['week'];
    $year = $_POST['year'];
    $month = $_POST['month'];

    $firstDayOfMonth = date('N', strtotime("$year-$month-01"));
    if ($firstDayOfMonth == 7) {
        $week++;
    }
    $query = "SELECT 
        WEEK(c.calendar_date, 1) AS semana,
        u2.id_user,
        u2.id_profile,
        (
            SELECT 
                SUM(
                    CASE 
                        WHEN u2.id_profile = 1 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, 0)
                        WHEN u2.id_profile = 2 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, IF(DAYNAME(c2.calendar_date) = 'Saturday', 4, 0))
                        WHEN u2.id_profile = 3 THEN 
                            IF(DAYNAME(c2.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), 8, 0)
                        ELSE 0
                    END
                ) AS total_valor_dia
            FROM 
                Calendar c2
            JOIN 
                Users u2 ON u2.id_user = ?
            WHERE 
                WEEKDAY(c2.calendar_date) BETWEEN 0 AND 5
                AND WEEK(c2.calendar_date, 1) = WEEK(DATE_ADD(CONCAT(?, '-', LPAD(?, 2, '0'), '-01'), INTERVAL (? - 1) WEEK), 1)
                AND YEAR(c2.calendar_date) = ?
                AND MONTH(c2.calendar_date) = ?
                AND c2.holiday = 0
        ) AS acumulado_valor_dia
    FROM 
        Calendar c
    JOIN 
        Users u2 ON u2.id_user = ?
    WHERE 
        WEEKDAY(c.calendar_date) BETWEEN 0 AND 5
        AND WEEK(c.calendar_date, 1) = WEEK(DATE_ADD(CONCAT(?, '-', LPAD(?, 2, '0'), '-01'), INTERVAL (? - 1) WEEK), 1)
        AND YEAR(c.calendar_date) = ?
        AND MONTH(c.calendar_date) = ?
        AND c.holiday = 0
    GROUP BY
        WEEK(c.calendar_date, 1),
        u2.id_user,
        u2.id_profile;
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssisssss", $userId, $year, $month, $week, $year, $month, $userId, $year, $month, $week, $year, $month);
    $stmt->execute();
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
        echo json_encode(array("success" => false, "message" => "No se encontraron resultados"));
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array("success" => false, "message" => "No se recibieron los parámetros necesarios en la solicitud POST"));
}
?>