<?php
session_start();
require_once '../../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['dni'];
    $pass = $_POST['pass'];

    $stmt = $conn->prepare("SELECT id_user, pass FROM Users WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_user, $stored_pass);
        $stmt->fetch();

        // Aquí comparas la contraseña ingresada con la contraseña almacenada en texto plano
        if ($pass == $stored_pass) { // Comprobación de contraseña en texto plano
            $_SESSION['user_id'] = $id_user;
            header("Location: /load");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "DNI no encontrado.";
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}
