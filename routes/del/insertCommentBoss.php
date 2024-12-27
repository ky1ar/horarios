<?php
require_once '../../includes/app/db.php'; // Verifica la ruta de tu archivo db.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificamos que los datos necesarios estén disponibles
    if (isset($_POST['user_id']) && isset($_POST['comentario'])) {
        $id_user = $_POST['user_id'];
        $comentario = trim($_POST['comentario']);  // Limpiar el comentario

        if (!empty($comentario)) {
            // Preparar la consulta SQL utilizando mysqli
            $sql = "INSERT INTO Comentarios (id_user, comentario) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // Enlazar los parámetros y ejecutar la consulta
                $stmt->bind_param("is", $id_user, $comentario); // "i" para enteros y "s" para string
                $stmt->execute();

                // Mensaje de éxito
                echo "Comentario agregado correctamente.";
                $stmt->close();
            } else {
                echo "Error al preparar la consulta: " . $conn->error;
            }
        } else {
            // Si el comentario está vacío, mostramos un error
            echo "El comentario no puede estar vacío.";
        }
    } else {
        echo "Faltan datos para agregar el comentario.";
    }
}

// Cerrar la conexión
$conn->close();
?>
