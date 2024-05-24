<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['date'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];

    $sql = "SELECT 
                c.id_date,
                c.calendar_date, 
                s.stamp
            FROM 
                Calendar c
            LEFT JOIN 
                Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
            WHERE 
                c.calendar_date = ?
            ORDER BY 
                c.calendar_date";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stamp = isset($row['stamp']) ? $row['stamp'] : '';
        echo json_encode(['success' => true, 'stamp' => $stamp]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No stamp found']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}

$conn->close();
