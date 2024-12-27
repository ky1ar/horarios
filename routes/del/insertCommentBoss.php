<?php
require_once '../../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['comentario'])) {
        $id_user = $_POST['user_id'];
        $comentario = trim($_POST['comentario']);

        if (!empty($comentario)) {
            $sql = "INSERT INTO Comentarios (id_user, comentario) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("is", $id_user, $comentario);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Faltan datos para agregar el comentario.";
    }
}

$conn->close();
?>
