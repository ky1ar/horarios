<?php
$currentPage = "Inicio";
require_once 'db.php';
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
    <!-- <section id="login">
        <div class="modal">
            <div class="left">
                <img src="assets/img/logod.webp" alt="Perfil">
                <div>
                    <h1>Bienvenido</h1>
                    <p>Ingresa tus datos para iniciar sesión.</p>
                </div>
                <form id="loginForm">
                    <div>
                        <label for="document">DNI</label>
                        <input name="document" type="text" placeholder="Ingresa tu DNI">
                    </div>
                    <div>
                        <label for="pass">Contraseña</label>
                        <input name="pass" type="password" placeholder="Ingresa tu contraseña">
                    </div>
                    <div id="errorDiv"></div>
                    <button type="submit">Iniciar sesión</button>
                </form>
            </div>
            <div class="right">
                <img src="assets/img/login.webp" alt="">
            </div>
        </div>
    </section> -->

    <section class="cont-login">
        <div class="login">
            <div>
                <img src="./assets/img/login-1.jpg" alt="">
            </div>
            <div>
                <img src="./assets/img/user-icon.png" alt="">
                <h1>Bienvenido</h1>
                <form action="">
                    <label for="">DNI</label>
                    <input type="text" placeholder="">
                    <label for="">Contraseña</label>
                    <input type="password" placeholder="">
                    <input type="submit" value="ACCEDER">
                </form>
            </div>
        </div>
    </section>
</body>

</html>
