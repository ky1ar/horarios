<?php
require_once '../../includes/app/db.php';

if (isset($_POST['userId']) && isset($_POST['date'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];

    $sql = "SELECT 
                c.id_date,
                c.calendar_date, 
                c.holiday,
                s.just,
                s.coment,
                s.stamp,
                s.mid_time,    
                s.full_time,
                s.salud,
                s.servicio     
            FROM 
                Calendar c
            LEFT JOIN 
                Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
            WHERE 
                c.calendar_date = ?
            ORDER BY 
                c.calendar_date";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['holiday'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Es un feriado']);
        } else {
            $stamp = isset($row['stamp']) ? $row['stamp'] : '';
            $just = isset($row['just']) ? $row['just'] : '';
            $coment = isset($row['coment']) ? $row['coment'] : '';
            $midTime = isset($row['mid_time']) ? $row['mid_time'] : 0;
            $fullTime = isset($row['full_time']) ? $row['full_time'] : 0;
            $salud = isset($row['salud']) ? $row['salud'] : 0;
            $servicio = isset($row['servicio']) ? $row['servicio'] : 0;
            
            echo json_encode([
                'success' => true,
                'stamp' => $stamp,
                'just' => $just,
                'coment' => $coment,
                'mid_time' => $midTime,
                'full_time' => $fullTime,
                'salud' => $salud,
                'servicio' => $servicio
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró ningún registro']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}

$conn->close();
