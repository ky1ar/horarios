<?php
require_once '../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {

    $date = $_POST['date'];
    $day = $_POST['day'];
    
    $today = new DateTime();
    $todayDay = $today->format('j');
    $todayMonth = $today->format('n');

    $selectedDate = new DateTime($date);
    $dayMonth = $selectedDate->format('j');
    $monthNumber = $selectedDate->format('n');

    for ($i = 0; $i < $day; $i++) { echo '<li></li>'; }
    
    $sql = "SELECT * FROM Calendar WHERE YEAR(calendar_date) = YEAR('$date') AND MONTH(calendar_date) = MONTH('$date')";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        
        
        $dayNum = date('d', strtotime($row['calendar_date']));
        $state = $row['state'];
        
        echo '<li' . (($todayMonth == $monthNumber && $todayDay == $dayNum) ? ' class="today"' : '') . '>';
        if ($state == 0 || ($todayMonth == $monthNumber && $dayNum <= $todayDay)) {
            echo '<span>'.$dayNum.'</span>';
        } else {
            echo '<div class="boxDay" data-day="'.$dayNum.'">'.$dayNum.'</div>';
        }
        echo '</li>';
    }
}
$conn->close();
?>



