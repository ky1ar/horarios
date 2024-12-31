<?php
require_once '../../includes/app/db.php';

if (isset($_POST['date']) && isset($_POST['userId'])) {
    $date = $_POST['date'];
    $userId = $_POST['userId'];

    $sql = "SELECT s.just
            FROM Schedule s
            JOIN Calendar c ON s.id_calendar = c.id_date
            WHERE s.id_user = ? AND c.calendar_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['just'])) {
            $justFile = $row['just'];
            $justFileUrl = 'justs/' . $justFile;
            echo json_encode(['success' => true, 'justFileUrl' => $justFileUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No justification found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}

$conn->close();
