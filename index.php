<?php
session_start();
require_once './includes/app/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://tiendakrear3d.com/wp-content/uploads/2020/08/cropped-identificador-de-logo-87-32x32-1.png">
    <title>Krear 3D | Horarios</title>
    <?php require_once 'header.php'; ?>
</head>

<body>
    <section class="cont-login">
        <div class="login">
            <div>
                <img class="logo" src="./assets/img/nlog5.svg" alt="">
                <img class="refer" src="./assets/img/horarios-refer.webp" alt="">
            </div>
            <div>
                <img src="./assets/img/login-user.webp" alt="">
                <h1>Bienvenido</h1>
                <form action="./routes/del/login.php" method="post">
                    <label for="dni">DNI</label>
                    <input type="text" name="dni" placeholder="" required>
                    <label for="pass">Contraseña</label>
                    <input type="password" name="pass" placeholder="" required>
                    <input type="submit" value="ACCEDER">
                </form>
            </div>
        </div>
    </section>
</body>

</html>