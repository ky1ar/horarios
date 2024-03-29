<?php
require_once '../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderNumber'], $_POST['document'] )) {
    
    $orderNumber    = mysqli_real_escape_string($conn, $_POST['orderNumber']);
    $document       = mysqli_real_escape_string($conn, $_POST['document']);
    
    $sql = "SELECT o.id FROM Orders o INNER JOIN Users c ON o.client = c.id WHERE o.number = ? AND c.document = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $orderNumber, $document);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $agent = $_SERVER['HTTP_USER_AGENT'];

        $ipReadable = inet_ntop(inet_pton($ip));
        $ipv4 = null;
        if (filter_var($ipReadable, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipv4 = $ipReadable;
        } elseif (filter_var($ipReadable, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipv4 = inet_ntop(substr(inet_pton($ipReadable), 12));
        }

        $sql = "INSERT INTO History (orders, actions, ip, agent) VALUES (?, 3, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $row['id'], $ipv4, $agent);
        $stmt->execute();

        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("success" => false, "message" => "Orden no encontrada, verifica los datos."));
    }
    $stmt->close();
    $result->close();
    $conn->close();

} else {
    echo json_encode(array("success" => false, "message" => "Datos incompletos."));
    exit();
}
?>
