<?php
require_once '../../includes/app/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

if (isset($_POST['user_id'], $_POST['comentario'], $_POST['autor'])) {
    $id_user = $_POST['user_id'];
    $comentario = trim($_POST['comentario']);
    $autor = $_POST['autor']; // Obtener el autor

    if (!empty($comentario)) {
        $sql = "INSERT INTO Comentarios (id_user, comentario, autor) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isi", $id_user, $comentario, $autor);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Comentario vacío.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos para agregar el comentario.']);
}

$conn->close();
