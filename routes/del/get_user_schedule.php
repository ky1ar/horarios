<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];

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


    $firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
    $lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

    $dayOfWeekFirst = date('N', strtotime($firstDayOfMonth));
    if ($dayOfWeekFirst == 7) {
        $startDate = date('Y-m-d', strtotime("$firstDayOfMonth + 1 day"));
    } else {
        $startDate = date('Y-m-d', strtotime("$firstDayOfMonth - " . ($dayOfWeekFirst - 1) . " days"));
    }

    $dayOfWeekLast = date('N', strtotime($lastDayOfMonth));
    if ($dayOfWeekLast >= 6) {
        $endDate = $lastDayOfMonth;
    } else {
        $daysToAdd = 6 - $dayOfWeekLast;
        $endDate = date('Y-m-d', strtotime("$lastDayOfMonth + $daysToAdd days"));
    }
    $sql = "SELECT 
    t.id_date,
    t.calendar_date,
    t.id_schedule,
    t.holiday,
    t.diff_hours_minutes_final,
    t.name,
    t.id_user,
    t.day_of_week_es,
    t.day_number,
    t.new_column,
    t.stamp,
    t.just,
    t.modified,
    t.mid_time,
    t.full_time,
    t.salud,
    t.servicio,
    CASE 
        WHEN t.new_column = 'DF' THEN 'DF'
        ELSE
            CASE 
                WHEN ? = 1 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
                    CONCAT(
                        CASE
                            WHEN TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00') >= 0 THEN '+'
                            ELSE '-'
                        END,
                        LPAD(
                            FLOOR(
                                ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) / 3600
                            ), 2, '0'
                        ), ':',
                        LPAD(
                            FLOOR(
                                (ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) % 3600) / 60
                            ), 2, '0'
                        )
                    )
                    WHEN ? = 1 AND t.day_of_week_es = 'Sábado' THEN 
    CONCAT(
        '+',
        LPAD(
            HOUR(STR_TO_DATE(t.new_column, '%H:%i')),
            2,
            '0'
        ),
        ':',
        LPAD(
            MINUTE(STR_TO_DATE(t.new_column, '%H:%i')),
            2,
            '0'
        )
    )
                WHEN ? = 2 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
                    CONCAT(
                        CASE
                            WHEN TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00') >= 0 THEN '+'
                            ELSE '-'
                        END,
                        LPAD(
                            FLOOR(
                                ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) / 3600
                            ), 2, '0'
                        ), ':',
                        LPAD(
                            FLOOR(
                                (ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) % 3600) / 60
                            ), 2, '0'
                        )
                    )
                WHEN ? = 2 AND t.day_of_week_es = 'Sábado' THEN
                    CONCAT(
                        CASE
                            WHEN TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('04:00') >= 0 THEN '+'
                            ELSE '-'
                        END,
                        LPAD(
                            FLOOR(
                                ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('04:00')) / 3600
                            ), 2, '0'
                        ), ':',
                        LPAD(
                            FLOOR(
                                (ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('04:00')) % 3600) / 60
                            ), 2, '0'
                        )
                    )
                WHEN ? = 3 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
                    CONCAT(
                        CASE
                            WHEN TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00') >= 0 THEN '+'
                            ELSE '-'
                        END,
                        LPAD(
                            FLOOR(
                                ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) / 3600
                            ), 2, '0'
                        ), ':',
                        LPAD(
                            FLOOR(
                                (ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) % 3600) / 60
                            ), 2, '0'
                        )
                    )
                WHEN ? = 3 AND t.day_of_week_es = 'Sábado' THEN
                    CONCAT(
                        CASE
                            WHEN TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00') >= 0 THEN '+'
                            ELSE '-'
                        END,
                        LPAD(
                            FLOOR(
                                ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) / 3600
                            ), 2, '0'
                        ), ':',
                        LPAD(
                            FLOOR(
                                 (ABS(TIME_TO_SEC(STR_TO_DATE(t.new_column, '%H:%i')) - TIME_TO_SEC('08:00')) % 3600) / 60
                            ), 2, '0'
                        )
                    )
                ELSE 'DF'
            END
            END AS time_difference
FROM 
    (
        SELECT
            c.id_date,
            c.calendar_date,
            c.holiday,
        	DAY(c.calendar_date) AS day_number,
            s.id_schedule,
        	s.stamp,
            s.just,
            s.modified,
            s.mid_time,
            s.full_time,
            s.salud,
            s.servicio,
            COALESCE(
                CASE 
                    WHEN LENGTH(s.stamp) > 10 THEN 
                        CONCAT(
                            FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                    (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))))) / 3600), 
                            ':', 
                            LPAD(FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                         (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i')))) % 3600) / 60), 2, '0')
                        )
                    ELSE 
                        CONCAT(
                            FLOOR((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) / 3600), 
                            ':', 
                            LPAD(FLOOR(((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) % 3600) / 60), 2, '0')
                        )
                END,
                'NULL' 
            ) AS diff_hours_minutes_final,
            u.name,
            u.id_user,
            CASE 
                WHEN DAYNAME(c.calendar_date) = 'Monday' THEN 'Lunes'
                WHEN DAYNAME(c.calendar_date) = 'Tuesday' THEN 'Martes'
                WHEN DAYNAME(c.calendar_date) = 'Wednesday' THEN 'Miércoles'
                WHEN DAYNAME(c.calendar_date) = 'Thursday' THEN 'Jueves'
                WHEN DAYNAME(c.calendar_date) = 'Friday' THEN 'Viernes'
                WHEN DAYNAME(c.calendar_date) = 'Saturday' THEN 'Sábado'
                WHEN DAYNAME(c.calendar_date) = 'Sunday' THEN 'Domingo'
                ELSE NULL
            END AS day_of_week_es,
            CASE 
                WHEN s.id_schedule IS NULL THEN 'DF'
                WHEN LENGTH(s.stamp) = 30 THEN 
                CONCAT(
                FLOOR(
                (TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - 
                TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i')) - 
                (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))) - 
                (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 21, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 16, 5), '%H:%i')))) / 3600), 
                ':', 
                LPAD(
                FLOOR(
                    ((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - 
                    TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i')) - 
                    (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))) - 
                    (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 21, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 16, 5), '%H:%i')))) % 3600) / 60), 
                    2, '0'))
                WHEN DAYNAME(c.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') THEN 
                    CASE 
                        WHEN LENGTH(s.stamp) = 20 THEN 
                            CONCAT(
                                FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                        (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))))) / 3600), 
                                ':', 
                                LPAD(FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                             (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i')))) % 3600) / 60), 2, '0')
                            )
                        ELSE 'DF'
                    END
                WHEN DAYNAME(c.calendar_date) = 'Saturday' THEN 
                    CASE 
                        WHEN ? IN (1, 2) AND LENGTH(s.stamp) IN (10, 20) THEN 
                            CASE 
                                WHEN LENGTH(s.stamp) = 20 THEN 
                                    CONCAT(
                                        FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                                (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))))) / 3600), 
                                        ':', 
                                        LPAD(FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                                     (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i')))) % 3600) / 60), 2, '0')
                                    )
                                WHEN LENGTH(s.stamp) = 10 THEN 
                                    CONCAT(
                                        FLOOR((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) / 3600), 
                                        ':', 
                                        LPAD(FLOOR(((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) % 3600) / 60), 2, '0')
                                    )
                                ELSE 'DF'
                            END
                        WHEN ? = 3 THEN 
                            CASE 
                                WHEN LENGTH(s.stamp) = 20 THEN 
                                    CONCAT(
                                        FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                                (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i'))))) / 3600), 
                                        ':', 
                                        LPAD(FLOOR((((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) - 
                                                     (TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 11, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(SUBSTRING(s.stamp, 6, 5), '%H:%i')))) % 3600) / 60), 2, '0')
                                    )
                                ELSE 'DF'
                            END
                        ELSE 'DF'
                    END
                ELSE 'DF'
            END AS new_column
        FROM 
            Calendar c
        LEFT JOIN Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
        LEFT JOIN Users u ON s.id_user = u.id_user
        WHERE
            c.calendar_date BETWEEN ? AND ?
        ORDER BY c.calendar_date
    ) AS t;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiiiiss", $scheduleType, $scheduleType, $scheduleType,$scheduleType, $scheduleType, $scheduleType, $scheduleType, $scheduleType, $userId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedule = array();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row;
    }
    echo json_encode(array('success' => true, 'schedule' => $schedule));
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibieron los parámetros necesarios.'));
}
