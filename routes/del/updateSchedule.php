<?php
require_once '../includes/app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $response = array();

    $scheduleId = $_POST['scheduleId'];
    $trainingWorker = $_POST['trainingWorker'];
    $meet = $_POST['meet'];

    $sql_email_and_name = "SELECT email, name FROM Training WHERE id = ?";
    $stmt_email_and_name = $conn->prepare($sql_email_and_name);
    $stmt_email_and_name->bind_param("i", $scheduleId);
    $stmt_email_and_name->execute();
    $stmt_email_and_name->bind_result($approved_email, $name);
    $stmt_email_and_name->fetch();
    $stmt_email_and_name->close();

    $sql_name = "SELECT name FROM Users WHERE id = ?";
    $stmt_name = $conn->prepare($sql_name);
    $stmt_name->bind_param("i", $trainingWorker);
    $stmt_name->execute();
    $stmt_name->bind_result($worker_name);
    $stmt_name->fetch();
    $stmt_name->close();

    $sql_date_and_time = "SELECT 
                        CASE DAYNAME(training_date)
                            WHEN 'Monday' THEN 'Lunes'
                            WHEN 'Tuesday' THEN 'Martes'
                            WHEN 'Wednesday' THEN 'Miércoles'
                            WHEN 'Thursday' THEN 'Jueves'
                            WHEN 'Friday' THEN 'Viernes'
                            WHEN 'Saturday' THEN 'Sábado'
                            WHEN 'Sunday' THEN 'Domingo'
                        END AS nombre_dia,
                        CASE MONTHNAME(training_date)
                            WHEN 'January' THEN 'Enero'
                            WHEN 'February' THEN 'Febrero'
                            WHEN 'March' THEN 'Marzo'
                            WHEN 'April' THEN 'Abril'
                            WHEN 'May' THEN 'Mayo'
                            WHEN 'June' THEN 'Junio'
                            WHEN 'July' THEN 'Julio'
                            WHEN 'August' THEN 'Agosto'
                            WHEN 'September' THEN 'Septiembre'
                            WHEN 'October' THEN 'Octubre'
                            WHEN 'November' THEN 'Noviembre'
                            WHEN 'December' THEN 'Diciembre'
                        END AS nombre_mes,
                        DATE_FORMAT(training_start, '%H:%i') AS hora_minutos
                    FROM Training
                    WHERE id = ?";
    $stmt_date_and_time = $conn->prepare($sql_date_and_time);
    $stmt_date_and_time->bind_param("i", $scheduleId);
    $stmt_date_and_time->execute();
    $stmt_date_and_time->bind_result($nombre_dia, $nombre_mes, $hora_minutos);
    $stmt_date_and_time->fetch();
    $stmt_date_and_time->close();

    $sql = "UPDATE Training SET worker = ?, meet = ?, training_state = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $trainingWorker, $meet, $scheduleId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {

        $title = 'Confirmación de Capacitación';
        $emailTemplate = '../includes/template/approvedSchedule.html';
        $htmlContent = file_get_contents($emailTemplate);
        $placeholders = array('%CLIENT%', '%MEET%', '%WORKER%', '%DIA%', '%MES%', '%HORA%');
        $values = array($name, $meet, $worker_name, $nombre_dia, $nombre_mes, $hora_minutos);
        $htmlContent = str_replace($placeholders, $values, $htmlContent);

        $emailHeader = "MIME-Version: 1.0" . "\r\n";
        $emailHeader .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $emailHeader .= "From: Krear 3D<web@soporte.krear3d.com>\r\n";
        $emailHeader .= "Reply-To: soporte@krear3d.com\r\n";

        $resultado = mail($approved_email, $title, $htmlContent, $emailHeader);

        if ($resultado) {
            $response['success'] = true;
            echo json_encode($response);
            exit();
        } else {
            $response['error'] = 'Error al enviar el correo electrónico';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['error'] = 'Error Interno';
        echo json_encode($response);
        exit();
    }
    $stmt->close();
    header('Content-Type: application/json');
}
