<?php
session_start();
require_once '../../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['dni'];
    $pass = $_POST['pass'];

    $stmt = $conn->prepare("SELECT id_user, pass, admin FROM Users WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_user, $stored_pass, $admin);
        $stmt->fetch();

        if ($pass == $stored_pass) {
            $_SESSION['user_id'] = $id_user;
            $_SESSION['admin'] = $admin; // Almacenar el estado de administrador en una variable de sesión
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
?>
