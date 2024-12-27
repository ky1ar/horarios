<?php
require_once '../../includes/app/db.php';

if (isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];

    $query = "SELECT c.comentario, DATE_FORMAT(c.created_at, '%d de %M del %Y') AS formatted_date
              FROM Comentarios c
              WHERE c.id_user = ?
              ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = [
                'comentario' => htmlspecialchars($row['comentario']),
                'created_at' => $row['formatted_date']
            ];
        }
        echo json_encode(['success' => true, 'comments' => $comments]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay comentarios disponibles']);
    }
    $stmt->close();
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
}
