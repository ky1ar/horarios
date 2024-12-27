<?php
require_once '../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificamos que los datos necesarios estén disponibles
    if (isset($_POST['user_id']) && isset($_POST['comentario'])) {
        $id_user = $_POST['user_id'];
        $comentario = trim($_POST['comentario']);  // Limpiar el comentario

        if (!empty($comentario)) {
            // Insertar comentario en la base de datos
            $sql = "INSERT INTO Comentarios (id_user, comentario) VALUES (:id_user, :comentario)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':comentario' => $comentario
            ]);

            // Mensaje de éxito
            echo "Comentario agregado correctamente.";
        } else {
            // Si el comentario está vacío, mostramos un error
            echo "El comentario no puede estar vacío.";
        }
    } else {
        echo "Faltan datos para agregar el comentario.";
    }
}
?>
