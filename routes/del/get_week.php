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
        WEEK(c.calendar_date, 1) AS semana,
        u2.id_user,
        u2.id_profile,
        SUM(
            CASE 
                WHEN u2.id_profile = 1 THEN 
                    IF(DAYNAME(c.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, 0)
                WHEN u2.id_profile = 2 THEN 
                    IF(DAYNAME(c.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 8, IF(DAYNAME(c.calendar_date) = 'Saturday', 4, 0))
                WHEN u2.id_profile = 3 THEN 
                    IF(DAYNAME(c.calendar_date) IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), 8, 0)
                ELSE 0
            END
        ) AS acumulado_valor_dia
    FROM 
        Calendar c
    JOIN 
        Users u2 ON u2.id_user = ?
    WHERE 
        WEEK(c.calendar_date, 1) = WEEK(DATE_ADD(CONCAT(?, '-', LPAD(?, 2, '0'), '-01'), INTERVAL (? - 1) WEEK), 1)
        AND YEAR(c.calendar_date) = ?
        AND MONTH(c.calendar_date) = ?
        AND (
            (
                WEEKDAY(c.calendar_date) BETWEEN 0 AND 5  -- Lunes a viernes
                AND u2.id_profile IN (1, 2)
            )
            OR
            (
                WEEKDAY(c.calendar_date) = 6  -- Sábado
                AND u2.id_profile = 2
            )
        )
        AND c.holiday = 0
    GROUP BY
        WEEK(c.calendar_date, 1),
        u2.id_user,
        u2.id_profile;";

    // Prepara y ejecuta la consulta
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $userId, $year, $month, $week, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica si hay resultados
    if ($result->num_rows > 0) {
        // Inicializa un array para almacenar los resultados
        $response = array();
        $response['success'] = true;
        $response['data'] = array();

        // Itera sobre los resultados
        while ($row = $result->fetch_assoc()) {
            // Agrega ":00" a la hora obtenida
            $row['acumulado_valor_dia'] .= ":00";
            $response['data'][] = $row;
        }

        // Envía la respuesta como JSON
        echo json_encode($response);
    } else {
        // Si no se encontraron resultados, envía un mensaje de error
        echo json_encode(array("success" => false, "message" => "No se encontraron resultados"));
    }

    // Cierra la conexión y el statement
    $stmt->close();
    $conn->close();
} else {
    // Si no se recibieron los parámetros necesarios, envía un mensaje de error
    echo json_encode(array("success" => false, "message" => "No se recibieron los parámetros necesarios en la solicitud POST"));
}
?>
