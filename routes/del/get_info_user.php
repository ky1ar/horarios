<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Obtener el último día laborable del mes anterior
    $lastWorkingDayOfLastMonthQuery = "SELECT MAX(calendar_date) AS last_working_day
        FROM Calendar
        WHERE calendar_date < DATE(CONCAT(?, '-', ?, '-01'))
            AND DAYOFWEEK(calendar_date) BETWEEN 2 AND 6
            AND holiday = 0;";
    $stmtLastDay = $conn->prepare($lastWorkingDayOfLastMonthQuery);
    $stmtLastDay->bind_param("si", $year, $month);
    $stmtLastDay->execute();
    $resultLastDay = $stmtLastDay->get_result();
    $lastWorkingDay = $resultLastDay->fetch_assoc()['last_working_day'];
    $stmtLastDay->close();

    // Ajustar el último día laborable del mes anterior
    if ($lastWorkingDay) {
        $lastWorkingDay = date('Y-m-d', strtotime($lastWorkingDay));
    } else {
        // Si no hay un último día laborable, asumir el primer día del mes actual
        $lastWorkingDay = date('Y-m-d', strtotime("$year-$month-01"));
    }

    // Obtener el penúltimo día del mes actual
    $penultimateDayOfMonth = date('Y-m-d', strtotime("last day of $year-$month -1 day"));

    // Consulta para calcular las horas ajustadas y el porcentaje de horas totales
    $query = "SELECT
        u.id_user AS id_user,
        u.id_profile,
        SUM(
            CASE
                WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                WHEN u.id_profile = 2 THEN
                    CASE
                        WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                        WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                        ELSE 0
                    END
                WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
                ELSE 0
            END
        ) AS total_hours_required,
        TIME_FORMAT(
            SEC_TO_TIME(
                CASE
                    WHEN SUM(
                            ROUND(
                                CASE
                                    WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    ELSE 0
                                END, 0)
                        ) > 6 THEN 
                        TIME_TO_SEC(
                            SEC_TO_TIME(
                                SUM(
                                    CASE
                                        WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                                        WHEN u.id_profile = 2 THEN
                                            CASE
                                                WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                                                WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                                                ELSE 0
                                            END
                                        WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
                                        ELSE 0
                                    END
                                ) * 60 * 60
                            )
                        ) + 
                        (SUM(
                            ROUND(
                                CASE
                                    WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                                    ELSE 0
                                END, 0)
                        ) - 6) * 15 * 60
                    ELSE
                        SUM(
                            CASE
                                WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                                WHEN u.id_profile = 2 THEN
                                    CASE
                                        WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                                        WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                                        ELSE 0
                                    END
                                WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
                                ELSE 0
                            END
                        ) * 60 * 60
                END
            ), '%H:%i'
        ) AS adjusted_hours,
        TIME_FORMAT(
            SEC_TO_TIME(
                SUM(
                    CASE
                        WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                        WHEN u.id_profile = 2 THEN
                            CASE
                                WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                                WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                                ELSE 0
                            END
                        WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
                        ELSE 0
                    END
                ) * 60 * 60 * 0.01
            ), '%H:%i'
        ) AS one_percent_total_hours
    FROM
        Calendar c
    JOIN
        Users u ON u.id_user = ?
    LEFT JOIN
        Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
    WHERE
        c.calendar_date BETWEEN ? AND ?
        AND c.holiday = 0
        AND c.calendar_date BETWEEN ? AND ?
    GROUP BY
        u.id_user,
        u.id_profile;";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissss", $userId, $userId, $lastWorkingDay, $penultimateDayOfMonth, $lastWorkingDay, $penultimateDayOfMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode($row);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
?>
