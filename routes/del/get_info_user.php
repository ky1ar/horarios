<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['month']) && isset($_POST['year'])) {
    $userId = $_POST['userId'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $currentDate = date('Y-m-d');

    // Preparar la consulta SQL
    $sql = "SET @year = ?;
            SET @month = ?;
            SET @id_user = ?;
            SET @current_date = CURDATE();";

    // Preparar y ejecutar la consulta SET
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $year, $month, $userId);
    $stmt->execute();

    // Preparar la consulta SQL
    $sql = "SELECT
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
    ) INTO @total_hours
FROM
    Calendar c
JOIN
    Users u ON u.id_user = @id_user
WHERE
    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
    AND c.holiday = 0;

-- Calcular la sumatoria de missing points antes de la fecha actual
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
    ) INTO @total_missing_points
FROM
    Calendar c
JOIN
    Users u ON u.id_user = @id_user
LEFT JOIN
    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
WHERE
    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
    AND c.calendar_date < @current_date
    AND c.holiday = 0;

-- Calcular la sumatoria de llegadas tarde (is_late) para el mes
SELECT
    SUM(
        CASE
            WHEN LEFT(s.stamp, 5) > '09:00' THEN 1
            ELSE 0
        END
    ) INTO @total_late_points
FROM
    Calendar c
JOIN
    Users u ON u.id_user = @id_user
LEFT JOIN
    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
WHERE
    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
    AND c.holiday = 0;

-- Calcular la sumatoria de ausencias injustificadas hasta la fecha actual
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
    ) INTO @total_unjustified_absences
FROM
    Calendar c
JOIN
    Users u ON u.id_user = @id_user
LEFT JOIN
    Schedule s ON c.id_date = s.id_calendar AND s.id_user = @id_user
WHERE
    c.calendar_date BETWEEN DATE(CONCAT(@year, '-', @month, '-01')) AND LAST_DAY(DATE(CONCAT(@year, '-', @month, '-01')))
    AND c.holiday = 0;

-- Consulta final para obtener las columnas requeridas
SELECT
    @id_user AS id_user,
    u.id_profile,
    @total_hours AS total_hours_required,
    @total_missing_points AS total_sin_registro,
    @total_late_points AS total_tardanzas,
    @total_unjustified_absences AS total_faltas_injustificadas
FROM
    Users u
WHERE
    u.id_user = @id_user;";

    // Preparar la consulta
    $result = $conn->query($sql);

    // Continuar con las demás consultas...

    // Obtener los resultados y generar la respuesta JSON
    $finalData = array();
    while ($row = $result->fetch_assoc()) {
        $finalData[] = $row;
    }
    echo json_encode(array('success' => true, 'data' => $finalData));
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibieron los parámetros necesarios.'));
}
