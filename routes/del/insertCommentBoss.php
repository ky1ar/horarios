<?php
require_once '../includes/app/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_POST['user_id'];
    $comentario = trim($_POST['comentario']); 

    if (!empty($comentario)) {
        $sql = "INSERT INTO Comentarios (id_user, comentario) VALUES (:id_user, :comentario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_user' => $id_user,
            ':comentario' => $comentario
        ]);
        echo "Comentario agregado correctamente.";
    } else {
        echo "El comentario no puede estar vacío.";
    }
}
?>
