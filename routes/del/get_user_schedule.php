<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../includes/app/db.php';

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Consulta para obtener el horario del usuario seleccionado
    $sql = "SELECT * FROM Schedule WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedule = array();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row; 
    }
    // echo json_encode(array('success' => true, 'schedule' => $schedule));
} else {
    echo json_encode(array('success' => false, 'message' => 'No se recibió el id del usuario.'));
}
?>
