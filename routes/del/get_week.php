<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../includes/app/db.php';

// Verifica si se han recibido los parámetros necesarios en la solicitud POST
if (isset($_POST['userId']) && isset($_POST['week']) && isset($_POST['year']) && isset($_POST['month'])) {
    // Obtiene los parámetros de la solicitud POST
    $userId = $_POST['userId'];
    $week = $_POST['week'];
    $year = $_POST['year'];
    $month = $_POST['month'];

    // Prepara la consulta SQL
    $query = "SELECT 
        WEEK(calendar_date, 1) AS semana,
        u2.id_user,
        u2.id_profile,
        (SELECT total_valor_dia FROM (
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
                AND WEEK(c2.calendar_date, 1) = ?
                AND YEAR(c2.calendar_date) = ?
                AND MONTH(c2.calendar_date) = ?
                AND c2.holiday = 0
        ) AS total) AS acumulado_valor_dia
    FROM 
        Calendar c
    JOIN 
        Users u2 ON u2.id_user = ?
    WHERE 
        WEEKDAY(c.calendar_date) BETWEEN 0 AND 5
        AND WEEK(c.calendar_date, 1) = ?
        AND YEAR(c.calendar_date) = ?
        AND MONTH(c.calendar_date) = ?
        AND c.holiday = 0
    GROUP BY
        WEEK(calendar_date, 1),
        u2.id_user,
        u2.id_profile";

    // Prepara y ejecuta la consulta
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiiiiii", $userId, $week, $year, $month, $userId, $week, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica si hay resultados
    if ($result->num_rows > 0) {
        // Itera sobre los resultados
        while ($row = $result->fetch_assoc()) {
            // Imprime los resultados
            echo "Semana: " . $row["semana"] . ", ID Usuario: " . $row["id_user"] . ", ID Profile: " . $row["id_profile"] . ", Acumulado: " . $row["acumulado_valor_dia"] . "<br>";
        }
    } else {
        echo "No se encontraron resultados";
    }

    // Cierra la conexión y el statement
    $stmt->close();
    $conn->close();
} else {
    echo "No se recibieron los parámetros necesarios en la solicitud POST.";
}
?>