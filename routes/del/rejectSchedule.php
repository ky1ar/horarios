<?php
require_once '../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();

    try {
        $conn->begin_transaction();

        $scheduleId = $_POST['scheduleId'];
        $rejectText = $_POST['rejectText'];
        $date = $_POST['date'];


        $sql_email = "SELECT email, name FROM Training WHERE id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $scheduleId);
        $stmt_email->execute();
        $stmt_email->bind_result($email, $name);
        $stmt_email->fetch();
        $stmt_email->close();

        $sql = "UPDATE Training SET details = ?, training_state = 3 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta para actualizar la capacitación");
        }
        $stmt->bind_param("si", $rejectText, $scheduleId);
        $stmt->execute();

        $result = $stmt->affected_rows;
        $stmt->close();
        if ($result <= 0) {
            throw new Exception("No se pudo actualizar el registro");
        }

        $sql = "UPDATE Calendar SET state = 1 WHERE calendar_date = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta para actualizar el calendario");
        }
        $stmt->bind_param("s", $date);
        $stmt->execute();

        $result = $stmt->affected_rows;
        $stmt->close();
        // if ($result <= 0) {
        //     throw new Exception("No se pudo actualizar el calendario");
        // }
        // Envío de correo electrónico
        $title = 'Eliminación de Capacitación';
        $emailTemplate = '../includes/template/rejectedSchedule.html';
        $htmlContent = file_get_contents($emailTemplate);
        $placeholders = array('%CLIENT%');
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
