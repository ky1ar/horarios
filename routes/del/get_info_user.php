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

    // Crear conexión y setear variables de sesión
    $conn->query("SET @year = $year");
    $conn->query("SET @month = $month");
    $conn->query("SET @id_user = $userId");
    $conn->query("SET @current_date = '$currentDate'");

    // Consulta para calcular todas las sumatorias requeridas
    $sql = "
        SELECT
            u.id_user,
            u.id_profile,
            (
                SELECT
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
                    )
                FROM
                    Calendar c
                WHERE
                    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
                    AND c.holiday = 0
            ) AS total_hours_required,
            (
                SELECT
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
                    )
                FROM
                    Calendar c
                LEFT JOIN
                    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
                WHERE
                    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
                    AND c.calendar_date < @current_date
                    AND c.holiday = 0
            ) AS total_sin_registro,
            (
                SELECT
                    SUM(
                        CASE
                            WHEN LEFT(s.stamp, 5) > '09:00' THEN 1
                            ELSE 0
                        END
                    )
                FROM
                    Calendar c
                LEFT JOIN
                    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
                WHERE
                    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
                    AND c.holiday = 0
            ) AS total_tardanzas,
            (
                SELECT
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
                                AND c.calendar_date < @current_date THEN 1
                            ELSE 0
                        END
                    )
                FROM
                    Calendar c
                LEFT JOIN
                    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
                WHERE
                    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
                    AND c.holiday = 0
            ) AS total_faltas_injustificadas
        FROM
            Users u
        WHERE
            u.id_user = @id_user
    ";

    $result = $conn->query($sql);

    $finalData = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $finalData[] = $row;
        }
        echo json_encode(array('success' => true, 'data' => $finalData));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Error en la consulta.'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibieron los parámetros necesarios.'));
}
