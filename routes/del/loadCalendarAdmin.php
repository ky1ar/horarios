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
        $date = $row['calendar_date'];
        $dayNum = date('d', strtotime($date));
        echo '<li class="admin ' . (($todayMonth == $monthNumber && $todayDay == $dayNum) ? 'today' : '') . '">';
        echo '<span data-day="'.$dayNum.'">'.$dayNum.'</span>';
        echo '<div class="calendarView">';
        
        $sql2 = 
        "SELECT t.id, t.training_state, w.name as w_name, m.model as m_model, m.slug as m_slug, 
        t.document as c_document, t.name as c_name, t.phone as c_phone, t.email as c_email, invoice, t.training_start
        FROM Training t 
        INNER JOIN Calendar c ON t.training_date = c.calendar_date 
        INNER JOIN Machine m ON t.machine = m.id 
        INNER JOIN Brand b ON m.brand = b.id 
        INNER JOIN Users w ON t.worker = w.id 
        WHERE (t.training_state = 1 OR t.training_state = 2) AND training_date = '$date' 
        ORDER BY training_start;";

        $result2 = $conn->query($sql2);
        if ($result2->num_rows > 0){
            while ($row2 = $result2->fetch_assoc()){
                echo '<div class="calendarViewRow '.(($row2['training_state'] == 2) ? 'finish' : '').'" data-id="'.$row2['id'].'" data-date="'.$date.'" data-start="'.$row2['training_start'].'">';
                echo '<h2>'.substr($row2['training_start'], 0, 5).'</h2>';
                echo '<div><h3>'.$row2['w_name'].'</h3>';
                echo '<p>'.$row2['m_model'].'</p></div>';
                echo '<img width="48" src="assets/mac/'.$row2['m_slug'].'.webp" alt="">';
                echo '</div>';
            }
        }
        echo '</div>';
        echo '</li>';
    }
}
$conn->close();
?>



