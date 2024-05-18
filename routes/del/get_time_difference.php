<?php
require_once 'db.php';

if (isset($_POST['userId']) && isset($_POST['calendarDate'])) {
    $userId = $_POST['userId'];
    $calendarDate = $_POST['calendarDate'];

    $query = "SELECT
        t.id_date,
        t.calendar_date,
        t.id_schedule,
        t.diff_hours_minutes_final,
        t.name,
        t.id_user,
        t.id_profile,
        t.day_of_week_es,
        t.new_column,
        CASE 
            WHEN t.new_column = 'DF' THEN 'DF'
            ELSE
                CASE 
                    WHEN t.id_profile = 1 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
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
                    WHEN t.id_profile = 1 AND t.day_of_week_es = 'Sábado' THEN t.new_column
                    WHEN t.id_profile = 2 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
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
                    WHEN t.id_profile = 2 AND t.day_of_week_es = 'Sábado' THEN
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
                    WHEN t.id_profile = 3 AND t.day_of_week_es IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') THEN
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
                    WHEN t.id_profile = 3 AND t.day_of_week_es = 'Sábado' THEN
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
                s.id_schedule,
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
                u.id_profile, 
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
                            WHEN LENGTH(s.stamp) = 10 THEN 
                                CONCAT(
                                    FLOOR((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) / 3600), 
                                    ':', 
                                    LPAD(FLOOR(((TIME_TO_SEC(STR_TO_DATE(RIGHT(s.stamp, 5), '%H:%i')) - TIME_TO_SEC(STR_TO_DATE(LEFT(s.stamp, 5), '%H:%i'))) % 3600) / 60), 2, '0')
                                )
                            ELSE 'DF'
                        END
                    ELSE 'DF'
                END AS new_column
            FROM 
                calendar c
            LEFT JOIN 
                schedule s ON c.id_date = s.id_date
            LEFT JOIN 
                users u ON s.id_user = u.id_user
        ) t
    WHERE t.id_user = ? AND t.calendar_date = ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("is", $userId, $calendarDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['success' => true, 'time_difference' => $row['time_difference']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
?>
