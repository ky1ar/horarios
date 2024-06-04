<?php
require_once '../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trainingId'])) {
    try {
        $trainingId = $_POST['trainingId'];
        $response = [];

        $days = array("domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado");
        $months = array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre",  "noviembre", "diciembre");

        $sql_training = "SELECT training_date, training_start, t.name as t_name, t.phone, m.model as m_model, m.slug as m_slug, w.name as w_name, meet FROM Training t INNER JOIN Calendar c ON t.training_date = c.calendar_date INNER JOIN Machine m ON t.machine = m.id INNER JOIN Brand b ON m.brand = b.id INNER JOIN Users w ON t.worker = w.id LEFT JOIN Default_Schedule ds ON t.training_start = ds.h_start AND c.custom = 0 LEFT JOIN Custom_Schedule cs ON t.training_start = cs.h_start AND c.custom = 1 WHERE t.id = ?";
        $stmt_training = $conn->prepare($sql_training);
        $stmt_training->bind_param("i", $trainingId);
        $stmt_training->execute();
        $result = $stmt_training->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            $selectedDate = new DateTime($row['training_date']);
            $month = $selectedDate->format('n');
            $dayName = $days[$selectedDate->format('w')];
            $day = $selectedDate->format('j');

            $meet_url = $row['meet'];
            if (!preg_match("~^(?:f|ht)tps?://~i", $meet_url)) {
                $meet_url = 'https://' . $meet_url;
            }
            $response['success'] = [
                'day' => $day,
                'dayName' => $dayName,
                'month' => $months[$month - 1],
                'schedule' => substr($row['training_start'], 0, 5),
                'name' => $row['t_name'],
                'phone' => $row['phone'],
                'model' => $row['m_model'],
                'slug' => $row['m_slug'],
                'worker' => $row['w_name'],
                'meet' => $meet_url
            ];
        } else {
            $response['success'] = false;
        }
        echo json_encode($response);
    } catch (Exception $e) {

        $response['error'] = $e->getMessage();
        echo json_encode($response);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn = null;
    }
} else {
    $response['error'] = 'Error Interno';
    echo json_encode($response);
}
