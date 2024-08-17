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

    if ($month == 6 && $year == 2024) {
        // Para junio de 2024, el día inicial será el 1 de junio
        $penultimateMP = "2024-06-01";
    } else {
        // Obtener el penúltimo día laborable del mes anterior
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

    // Obtener el penúltimo día laborable del mes
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
    // Consulta principal
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
    SUM(
        ROUND(
            CASE
                WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                ELSE 0
            END, 0)
        ) + COALESCE(
            SUM(
                CASE
                    WHEN c.calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND DATE_SUB((SELECT MAX(stamp_date) FROM Archivos), INTERVAL 1 DAY)
                    THEN s.calc_diff ELSE 0 END), 0
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
    CASE
    WHEN SUM(
            ROUND(
                CASE
                    WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    ELSE 0
                END, 0)
        ) > 6 THEN 
        TIME_FORMAT(
            SEC_TO_TIME(
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
                            WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < (SELECT MAX(stamp_date) FROM Archivos) THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            ELSE 0
                        END, 0)
                ) - 6) * (15 * 60)
            ), '%H:%i'
        )
    ELSE
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
                ) * 60 * 60
            ), '%H:%i'
        )
    END AS adjusted_hours,
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
GROUP BY
    u.id_user,
    u.id_profile;";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiss", $year, $month, $userId, $userId, $penultimateMP, $penultimateWorkday);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode($row);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
