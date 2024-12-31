<?php
require_once '../../includes/app/db.php';

// Configuración para mostrar errores (útil para depuración)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica si se envió el parámetro `userId`
if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Consulta SQL para obtener el campo `stamp` para la fecha `2024-12-30`
    $query = "
        SELECT s.stamp
        FROM Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        WHERE s.id_user = ? AND c.calendar_date = '2024-12-30'
        LIMIT 1;
    ";

    // Prepara y ejecuta la consulta
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verifica si se obtuvo un resultado
        if ($row = $result->fetch_assoc()) {
            $stamp = $row['stamp'] ?? null;
            echo json_encode(['success' => true, 'stamp' => $stamp]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No data found for the given user and date.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing userId parameter.']);
}

$conn->close();
?>
