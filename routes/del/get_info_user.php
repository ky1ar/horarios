<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $currentDate = date('Y-m-d');

    $sql1 = "SELECT
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
        ) AS total_hours
    FROM
        Calendar c
    JOIN
        Users u ON u.id_user = ?
    WHERE
        c.calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
        AND c.holiday = 0";

    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("issss", $userId, $year, $month, $year, $month);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $totalHours = $result1->fetch_assoc()['total_hours'];

    // Calcular la sumatoria de missing points antes de la fecha actual
    $sql2 = "SELECT
        SUM(
            ROUND(
                CASE
                    WHEN u.id_profile = 1 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    WHEN u.id_profile = 2 THEN
                        CASE
                            WHEN DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 6 THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            WHEN DAYOFWEEK(c.calendar_date) = 7 THEN GREATEST(0, (10 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                            ELSE 0
                        END
                    WHEN u.id_profile = 3 AND DAYOFWEEK(c.calendar_date) BETWEEN 2 AND 7 THEN GREATEST(0, (20 - COALESCE(LENGTH(s.stamp), 0)) / 5)
                    ELSE 0
                END, 0)
        ) AS total_missing_points
    FROM
        Calendar c
    JOIN
        Users u ON u.id_user = ?
    LEFT JOIN
        Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
    WHERE
        c.calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
        AND c.calendar_date < ?
        AND c.holiday = 0";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("iisssssss", $userId, $userId, $year, $month, $year, $month, $currentDate);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $totalMissingPoints = $result2->fetch_assoc()['total_missing_points'];

    // Calcular la sumatoria de llegadas tarde (is_late) para el mes
    $sql3 = "SELECT
        SUM(
            CASE
                WHEN LEFT(s.stamp, 5) > '09:00' THEN 1
                ELSE 0
            END
        ) AS total_late_points
    FROM
        Calendar c
    JOIN
        Users u ON u.id_user = ?
    LEFT JOIN
        Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
    WHERE
        c.calendar_date BETWEEN DATE(CONCAT(?, '-', ?, '-01')) AND LAST_DAY(DATE(CONCAT(?, '-', ?, '-01')))
        AND c.holiday = 0";

    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("iissss", $userId, $userId, $year, $month, $year, $month);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $totalLatePoints = $result3->fetch_assoc()['total_late_points'];

    // Calcular la sumatoria de ausencias injustificadas hasta la fecha actual
    $sql4 = "SELECT
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
                    AND c.calendar_date < ? THEN 1
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
        AND c.holiday = 0";

    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param("siisssssss", $currentDate, $userId, $userId, $year, $month, $year, $month);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    $totalUnjustifiedAbsences = $result4->fetch_assoc()['total_unjustified_absences'];

    // Consulta final para obtener las columnas requeridas
    $sqlFinal = "SELECT
        ? AS id_user,
        u.id_profile,
        ? AS total_hours_required,
        ? AS total_missing_points,
        ? AS total_late_points,
        ? AS total_unjustified_absences
    FROM
        Users u
    WHERE
        u.id_user = ?";

    $stmtFinal = $conn->prepare($sqlFinal);
    $stmtFinal->bind_param("iiiiii", $userId, $totalHours, $totalMissingPoints, $totalLatePoints, $totalUnjustifiedAbsences, $userId);
    $stmtFinal->execute();
    $resultFinal = $stmtFinal->get_result();

    $finalData = array();
    while ($row = $resultFinal->fetch_assoc()) {
        $finalData[] = $row;
    }
    echo json_encode(array('success' => true, 'data' => $finalData));
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibieron los parámetros necesarios.'));
}
?>
