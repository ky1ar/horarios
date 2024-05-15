<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && is_numeric($_POST['userId'])) {
    $userId = $_POST['userId'];

    try {
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

        if (count($schedule) > 0) {
            echo json_encode(array('success' => true, 'schedule' => $schedule));
        } else {
            echo json_encode(array('success' => false, 'message' => 'No se encontraron registros de horario para el usuario.'));
        }
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'message' => 'Error al ejecutar la consulta: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'El id del usuario no es válido.'));
}
?>
