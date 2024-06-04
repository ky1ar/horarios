<?php
require_once '../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $response = array();

    $schedule = $_POST['schedule'];
    $date = $_POST['date'];
    $count = $_POST['count'];

    $dniRUC = $_POST['dniRUC'];
    $client = $_POST['client'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $machineId = $_POST['machineId'];

    $invoice = $_FILES['invoice'];
    $fileName = $invoice['name'];
    $tempFileName = $invoice['tmp_name'];

    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExt;

    $path = '../uploads/invoices/';
    $invoicePath = $path . $uniqueFileName;
    move_uploaded_file($tempFileName, $invoicePath);

    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Training WHERE document = ? AND training_state IN (0, 1)");
    $stmt->bind_param("s", $dniRUC);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $existingCount = $row['count'];
    $stmt->close();
    
    if ($existingCount > 0) {
        $response['error'] = "Ya existe una capacitación registrada para este número de documento";
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();

    try {

        $stmt = $conn->prepare("SELECT COUNT(*) as count
        FROM Training 
        WHERE training_date = ? 
        AND training_start = ?
        AND (training_state != 3 AND training_state != 4)");
        $stmt->bind_param("ss", $date, $schedule);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $existingCount = $row['count'];
        $stmt->close();

        if ($existingCount > 0) {
            $response['error'] = "Ya existe una capacitación registrada para esta fecha y horario";
            echo json_encode($response);
            exit();
        }

        $sql = "INSERT INTO Training (machine, document, name, phone, email, invoice, training_date, training_start, training_state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssss", $machineId, $dniRUC, $client, $phone, $email, $invoicePath, $date, $schedule);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        if ($count == 1) {
            $sql = "UPDATE Calendar SET state = 0 WHERE calendar_date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $stmt->close();
        }

        $title = 'Solicitud de Capacitación en Revisión';
        $emailTemplate = '../includes/template/registerSchedule.html';
        $htmlContent = file_get_contents($emailTemplate);
        $placeholders = array('%CLIENT%');
        $values = array($client);
        $htmlContent = str_replace($placeholders, $values, $htmlContent);

        // Cabeceras para el correo electrónico
        $emailHeader = "MIME-Version: 1.0" . "\r\n";
        $emailHeader .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $emailHeader .= "From: Krear 3D<web@soporte.krear3d.com>\r\n";
        $emailHeader .= "Reply-To: soporte@krear3d.com\r\n";

        // Envío del correo
        $resultado = mail($email, $title, $htmlContent, $emailHeader);

        // Verificar si el correo se envió correctamente
        if ($resultado) {
            $response['success'] = '
            <div id="successSchedule">
                <div class="dot"></div>
                <img src="assets/img/inbox.svg" alt="ico" width="92" height="92">
                <h2>Reserva en revisión</h2>
                <p>Su reserva está en verificación. Pronto recibirá un correo con el enlace de la reunión y los detalles del responsable. ¡Gracias!</p>
            </div>';
            echo json_encode($response);
            exit();
        }
    } catch (Exception $e) {

        $conn->rollback();
        //$response = array("error" => "Error en el registro: " . $e->getMessage());
        $response['error'] = 'Error Interno';
        echo json_encode($response);
        exit();
    }
}
