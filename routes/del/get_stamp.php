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
                s.stamp
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
            echo json_encode(['success' => true, 'stamp' => $stamp, 'just' => $just]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró ningún registro']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}

$conn->close();
?>
