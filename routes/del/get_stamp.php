<?php
require_once '../../includes/app/db.php';

$userId = $_POST['userId'];
$date = $_POST['date'];

$query = $pdo->prepare("SELECT 
        c.id_date,
        c.calendar_date, 
        s.stamp
    FROM 
        Calendar c
    LEFT JOIN 
        Schedule s ON c.id_date = s.id_calendar AND s.id_user = ?
    WHERE 
        c.calendar_date = ?
    ORDER BY 
        c.calendar_date
");

$query->execute([$userId, $date]);

if ($query->rowCount() > 0) {
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $stamp = $result['stamp'] ?? '';
    echo json_encode(['success' => true, 'stamp' => $stamp]);
} else {
    echo json_encode(['success' => false, 'message' => 'No stamp found']);
}
