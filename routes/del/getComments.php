<?php
require_once '../../includes/app/db.php';

if (isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];  // Recibe el ID de usuario

    $query = "SELECT c.comentario
              FROM Comentarios c
              WHERE c.id_user = ?
              ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id_user);  // Se vincula el ID del usuario al parámetro de la consulta
    $stmt->execute();
    
    $result = $stmt->get_result();
    $comments = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        // Si hay comentarios, los agregamos al array
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = htmlspecialchars($row['comentario']);  // Escapamos caracteres especiales
        }
        echo json_encode(['success' => true, 'comments' => $comments]);  // Respondemos con los comentarios
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay comentarios disponibles']);  // Si no hay comentarios
    }
    
    $stmt->close();
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);  // Si no se pasa el ID
}
