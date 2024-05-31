<?php
$currentPage = "Inicio";
require_once 'db.php';
?>
</head>

<body>
    <section id="login">
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
    </section>
</body>

</html>
