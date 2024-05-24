<?php
require_once '../../includes/app/db.php';

$userId = $_POST['userId'];
$date = $_POST['date'];
$stamp = $_POST['stamp'];

$query = $pdo->prepare("SELECT s.* 
    FROM Schedule s
    JOIN Calendar c ON s.id_calendar = c.id_date
    WHERE s.id_user = ? AND c.calendar_date = ?
");
$query->execute([$userId, $date]);

if ($query->rowCount() > 0) {
    // Actualizar el stamp existente
    $updateQuery = $pdo->prepare("UPDATE Schedule s
        JOIN Calendar c ON s.id_calendar = c.id_date
        SET s.stamp = ?
        WHERE s.id_user = ? AND c.calendar_date = ?
    ");
    if ($updateQuery->execute([$stamp, $userId, $date])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
    }
} else {
    // Insertar un nuevo registro de stamp
    $insertQuery = $pdo->prepare("INSERT INTO Schedule (id_user, id_calendar, stamp)
        SELECT ?, c.id_date, ?
        FROM Calendar c
        WHERE c.calendar_date = ?
    ");
    if ($insertQuery->execute([$userId, $stamp, $date])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
    }
}
