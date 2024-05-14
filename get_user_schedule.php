<?php
// Incluye el archivo de conexión a la base de datos
require_once 'db.php';

// Verifica si se recibió el id del usuario en la solicitud AJAX
if (isset($_POST['userId'])) {
    // Obtiene el id del usuario de la solicitud
    $userId = $_POST['userId'];

    // Consulta para obtener el horario del usuario seleccionado
    $sql = "SELECT * FROM Schedule WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepara un array para almacenar los datos del horario del usuario
    $schedule = array();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row; // Agrega cada registro de horario al array
    }

    // Envía los datos del horario como respuesta en formato JSON
    echo json_encode(array('success' => true, 'schedule' => $schedule));
} else {
    // Si no se recibió el id del usuario, devuelve un mensaje de error
    echo json_encode(array('success' => false, 'message' => 'No se recibió el id del usuario.'));
}
?>
