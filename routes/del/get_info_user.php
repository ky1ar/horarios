<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $currentDate = date('Y-m-d');

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

    if ($month == 6 && $year == 2024) {
        $penultimateMP = "2024-06-01";
    } else {
        $penultimateQueryMonthPast = "
            SELECT calendar_date AS last_working_day_previous_month
            FROM Calendar
            WHERE holiday = 0
            AND DAYOFWEEK(calendar_date) <> 1
            AND calendar_date BETWEEN DATE_SUB(DATE(CONCAT(?, '-', ?, '-01')), INTERVAL 1 MONTH) AND LAST_DAY(DATE_SUB(DATE(CONCAT(?, '-', ?, '-01')), INTERVAL 1 MONTH))
            ORDER BY calendar_date DESC
            LIMIT 1;
        ";

        $stmt = $conn->prepare($penultimateQueryMonthPast);
        $stmt->bind_param("ssss", $year, $month, $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $penultimateWorkdayMP = $result->fetch_assoc();
        $penultimateMP = $penultimateWorkdayMP['last_working_day_previous_month'];
    }

    $penultimateQuery = "
            SELECT calendar_date AS penultimate_workday
            FROM (
                SELECT calendar_date
                FROM Calendar
                WHERE holiday = 0
                AND calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
                AND DAYOFWEEK(calendar_date) <> 1
                ORDER BY calendar_date DESC
                LIMIT 1 OFFSET 1
            ) AS subquery;
        ";

    $stmt = $conn->prepare($penultimateQuery);
    $stmt->bind_param("ssss", $year, $month, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $penultimateWorkdayRow = $result->fetch_assoc();
    $penultimateWorkday = $penultimateWorkdayRow['penultimate_workday'];


    $query = "SELECT
    u.id_user AS id_user,
    u.id_profile,
    SUM(
        CASE
            WHEN ? = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
            WHEN ? = 2 THEN
                CASE
                    WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                    WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                    ELSE 0
                END
            WHEN ? = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
            ELSE 0
        END
    ) AS total_hours_required,
    SUM(
        ROUND(
            CASE
                WHEN ? = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN ? = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN ? = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN ? = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                ELSE 0
            END, 0)
        ) + COALESCE(
            SUM(
                CASE
                    WHEN c.calendar_date BETWEEN ? AND DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY)
                    THEN s.calc_diff ELSE 0
                END
            ), 0
        ) AS total_missing_points,
    SUM(
        CASE
            WHEN (LEFT(s.stamp, 5) > (CASE WHEN u.id_user = 13 OR c.calendar_date = '2024-07-06' THEN '10:00' ELSE '09:00' END)) AND c.calendar_date < CURDATE() THEN 1
            ELSE 0
        END
    ) AS total_late_points,
    TIME_FORMAT(
        SEC_TO_TIME(
            SUM(
                CASE
                    WHEN LEFT(s.stamp, 5) > (CASE WHEN u.id_user = 13 OR c.calendar_date = '2024-07-06' THEN '10:00' ELSE '09:00' END) THEN 
                        TIME_TO_SEC(LEFT(s.stamp, 5)) - TIME_TO_SEC(CASE WHEN u.id_user = 13 OR c.calendar_date = '2024-07-06' THEN '10:00' ELSE '09:00' END)
                    ELSE 0
                END
            )
        ), '%H:%i'
    ) AS total_minutes_late_formatted,
    TIME_FORMAT(
        SEC_TO_TIME(
            SUM(
                CASE
                    WHEN ? = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                    WHEN ? = 2 THEN
                        CASE
                            WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                            WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                            ELSE 0
                        END
                    WHEN ? = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
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
GROUP BY
    u.id_user;";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiiiisiiiiiss", $scheduleType, $scheduleType, $scheduleType, $scheduleType, $scheduleType, $scheduleType, $scheduleType, $penultimateMP, $scheduleType, $scheduleType, $scheduleType, $userId, $userId, $penultimateMP, $penultimateWorkday);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode($row);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
