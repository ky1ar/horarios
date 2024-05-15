<?php
// Inicia el buffer de salida
ob_start();

// Incluye el archivo de conexión a la base de datos
require_once 'C:\xampp\htdocs\horarios\db.php';

// Prepara la respuesta en un array
$response = array('success' => false, 'message' => 'Unknown error');

try {
    // Verifica si se recibió el id del usuario en la solicitud AJAX
    if (isset($_POST['userId'])) {
        // Obtiene el id del usuario de la solicitud
        $userId = $_POST['userId'];

        // Consulta para obtener el horario del usuario seleccionado
        $sql = "SELECT * FROM Schedule WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Prepara un array para almacenar los datos del horario del usuario
        $schedule = array();
        while ($row = $result->fetch_assoc()) {
            $schedule[] = $row; // Agrega cada registro de horario al array
        }

        // Envía los datos del horario como respuesta en formato JSON
        $response = array('success' => true, 'schedule' => $schedule);
    } else {
        // Si no se recibió el id del usuario, devuelve un mensaje de error
        $response['message'] = 'No se recibió el id del usuario.';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Limpia el buffer de salida
ob_end_clean();

// Envía la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
