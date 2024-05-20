<?php
require_once '../../includes/app/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    date_default_timezone_set('America/Lima');
    $currentDate = date('Y-m-d');

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
                WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 2 AND DAYOFWEEK(c.calendar_date) = 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 AND c.calendar_date < CURDATE() THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                ELSE 0
            END, 0)
    ) AS total_missing_points,
    SUM(
        CASE
            WHEN LEFT(s.stamp, 5) > '09:00' THEN 1
            ELSE 0
        END
    ) AS total_late_points,
    SUM(
        CASE
            WHEN (CASE
                    WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                    WHEN u.id_profile = 2 THEN
                        CASE
                            WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN 8
                            WHEN DAYOFWEEK(c.calendar_date) = 7 THEN 4
                            ELSE 0
                        END
                    WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN 8
                    ELSE 0
                END) > 0
                AND (s.stamp IS NULL OR LENGTH(s.stamp) = 0)
                AND (c.just IS NULL OR c.just = '')
                AND c.calendar_date < CURDATE() THEN 1
            ELSE 0
        END
    ) AS total_unjustified_absences
FROM
    Calendar c
JOIN
    Users u ON u.id_user = ?
LEFT JOIN
    Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
WHERE
    c.calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
    AND c.holiday = 0
GROUP BY
    u.id_user,
    u.id_profile;";

$stmt = $conn->prepare($query);
$stmt->bind_param("iissss", $userId, $userId, $year, $month, $year ,$month);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode($row);
} else {
echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
?>