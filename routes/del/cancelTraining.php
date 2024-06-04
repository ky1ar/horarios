<?php
require_once '../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['trainingId'])) {
    $response = array();

    try {
        $conn->begin_transaction();

        $trainingId = $_POST['trainingId'];

        $sql_training = "SELECT training_date, email, name FROM Training WHERE id = ?";
        $stmt_training = $conn->prepare($sql_training);
        $stmt_training->bind_param("i", $trainingId);
        $stmt_training->execute();
        $stmt_training->bind_result($training_date, $email, $name);
        $stmt_training->fetch();
        $stmt_training->close();

        $sql_update = "UPDATE Training SET training_state = 4 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception("Error en la preparación de la consulta para actualizar la capacitación");
        }
        $stmt_update->bind_param("i", $trainingId);
        $stmt_update->execute();

        $result = $stmt_update->affected_rows;
        $stmt_update->close();

        if ($result <= 0) {
            throw new Exception("No se pudo actualizar el registro");
        }
        $sql_calendar = "UPDATE Calendar SET state = 1 WHERE calendar_date = ?";
        $stmt_calendar = $conn->prepare($sql_calendar);
        if (!$stmt_calendar) {
            throw new Exception("Error en la preparación de la consulta para actualizar el calendario");
        }
        $stmt_calendar->bind_param("s", $training_date);
        $stmt_calendar->execute();

        $result = $stmt_calendar->affected_rows;
        $stmt_calendar->close();
        /*if ($result <= 0) {
            throw new Exception("No se pudo actualizar el calendario");
        }*/
        
        $title = 'Eliminación de Capacitación';
        $emailTemplate = '../includes/template/cancelTraining.html';
        $htmlContent = file_get_contents($emailTemplate);
        $placeholders = array('%CLIENT%',);
        $values = array($name);
        $htmlContent = str_replace($placeholders, $values, $htmlContent);

        $emailHeader = "MIME-Version: 1.0" . "\r\n";
        $emailHeader .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $emailHeader .= "From: Krear 3D<web@soporte.krear3d.com>\r\n";
        $emailHeader .= "Reply-To: soporte@krear3d.com\r\n";

        $resultado = mail($email, $title, $htmlContent, $emailHeader);

        if ($resultado) {
            $response['success'] = true;
        } else {
            throw new Exception("Error al enviar el correo electrónico");
        }

        $conn->commit();
        echo json_encode($response);
    } catch (Exception $e) {
        $conn->rollback();

        $response['error'] = $e->getMessage();
        echo json_encode($response);
    } finally {
        $conn->close();
    }
} else {
    $response['error'] = "Solicitud no válida";
    echo json_encode($response);
}
