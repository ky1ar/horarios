<?php
// Incluir la conexión a la base de datos
require_once '../../includes/app/db.php';

// Verificar si se recibieron los parámetros necesarios
if (isset($_POST['userId']) && isset($_POST['date']) && isset($_POST['stamp'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];
    $stamp = $_POST['stamp'];

    // Consulta para verificar si ya existe un registro para el usuario y la fecha
    $sql = "SELECT s.id_schedule
            FROM Schedule s
            JOIN Calendar c ON s.id_calendar = c.id_date
            WHERE s.id_user = ? AND c.calendar_date = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar el stamp existente
        $row = $result->fetch_assoc();
        $idSchedule = $row['id_schedule'];

        $updateSql = "UPDATE Schedule SET stamp = ? WHERE id_schedule = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $stamp, $idSchedule);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
        }
        $updateStmt->close();
    } else {
        // Insertar un nuevo registro de stamp
        $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp)
                      SELECT ?, c.id_date, ?
                      FROM Calendar c
                      WHERE c.calendar_date = ?";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iss", $userId, $stamp, $date);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
        }
        $insertStmt->close();
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}

$conn->close();
?>
