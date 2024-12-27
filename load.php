<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'db.php';
$rango =  $_SESSION['admin'];
$id = $_SESSION['user_id'];
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

<body style="display: flex; flex-direction: column;">
    <div class="out">
        <a href="./routes/del/logout.php"><img src="./assets/img/out.svg" alt=""></a>
    </div>
    <div class="cont-insert" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">
        <h1>Insertar Registro</h1>
        <form id="uploadForm" action="./routes/del/cargarRegistro.php" method="post" enctype="multipart/form-data" class="form-insert">
            <label for="fileInput" class="custom-file-upload insert">Selecciona</label>
            <input type="file" id="fileInput" name="fileInput" accept=".csv" style="display: none;" required>
            <input type="submit" value="Cargar">
        </form>
    </div>


    <!-- <aside id="ky1-lft">
        <a href="" class="ky1-lgo"><img src="assets/img/logod.webp" alt=""></a>
        <ul class="ky1-lst">
            <li>
            </li>
        </ul>
    </aside> -->
    <section id="ky1-rgt">
        <header>
            <div class="ky1-ttl">
                <h1>Horarios Test</h1>
                <span>Registro biométrico del mes</span>
            </div>
            <div class="ky1-permisos">
                <div class="fond"></div>
                <img class="desc" src="assets/img/descanso-medico.webp" alt="">
                <button><img src="assets/img/descanso-medico.png" alt=""></button>
                <a href="assets/img/solicitud-permiso.pdf" download><img src="assets/img/formato-permiso.png" alt=""></a>
            </div>

            <div class="ky1-dte">
                <img id="previousMonth" src="assets/img/r.svg" width="12" height="12" alt="">
                <img src="assets/img/cal.svg" width="20" height="20" alt="">
                <span>Enero, 2024</span>
                <img id="nextMonth" src="assets/img/r.svg" width="12" height="12" alt="">
            </div>
            <div class="ky1-usr">
                <?php if ($rango == 1) : ?>
                    <div class="usr-btn" id="previousUser">
                        <img src="assets/img/r.svg" width="12" height="12" alt="">
                    </div>
                    <div class="usr-btn" id="nextUser">
                        <img src="assets/img/r.svg" width="12" height="12" alt="">
                    </div>
                <?php endif; ?>
                <div id="selectedUser" data-id="">
                    <img id="userImage" src="" alt="">
                    <span>
                        <h3 id="userName"></h3>
                        <h4 id="userCategory"></h4>
                    </span>
                    <?php if ($rango == 1) : ?>
                        <div id="userList">
                            <ul>
                                <?php
                                $firstIndex = true;
                                $sql = "SELECT u.id_user, u.slug, u.name, a.name as area FROM Users u INNER JOIN Profile p ON u.id_profile = p.id_profile INNER JOIN Area a ON u.id_area = a.id_area WHERE u.id_user != 20 ORDER BY u.name";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) : ?>
                                    <li <?php echo $firstIndex ? 'class="active"' : '' ?> data-id="<?php echo $row['id_user'] ?>" data-slug="<?php echo $row['slug'] ?>" data-name="<?php echo $row['name'] ?>" data-category="<?php echo $row['area'] ?>">
                                        <img src="assets/img/profiles/<?php echo $row['slug'] ?>.png" alt="">
                                        <h3><?php echo $row['name'] ?></h3>
                                    </li>
                                <?php
                                    $firstIndex = false;
                                endwhile;
                                ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($rango == 0) : ?>
                        <?php
                        $sql = "SELECT u.id_user, u.slug, u.name, a.name as area 
                                FROM Users u 
                                INNER JOIN Profile p ON u.id_profile = p.id_profile 
                                INNER JOIN Area a ON u.id_area = a.id_area 
                                WHERE u.id_user = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();

                        $sql = "SELECT hijos FROM Users WHERE id_user = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $stmt->bind_result($hijos);
                        $stmt->fetch();
                        $stmt->close();
                        ?>
                        <div id="userList" <?php echo ($hijos === null || empty($hijos)) ? 'style="display: none;"' : ''; ?>>
                            <ul>
                                <li class="active" data-id="<?php echo $row['id_user'] ?>" data-slug="<?php echo $row['slug'] ?>" data-name="<?php echo $row['name'] ?>" data-category="<?php echo $row['area'] ?>">
                                    <img src="assets/img/profiles/<?php echo $row['slug'] ?>.png" alt="">
                                    <h3><?php echo $row['name'] ?></h3>
                                </li>
                                <?php
                                if ($hijos !== null && !empty($hijos)) {
                                    $hijosArray = explode(',', $hijos);
                                    $inClause = str_repeat('?,', count($hijosArray) - 1) . '?';
                                    $params = str_repeat('i', count($hijosArray));

                                    $sql = "SELECT u.id_user, u.slug, u.name, a.name as area 
                                            FROM Users u 
                                            INNER JOIN Profile p ON u.id_profile = p.id_profile 
                                            INNER JOIN Area a ON u.id_area = a.id_area 
                                            WHERE u.id_user IN ($inClause) 
                                            ORDER BY u.name";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param($params, ...$hijosArray);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = $result->fetch_assoc()) :
                                ?>
                                        <li data-id="<?php echo $row['id_user'] ?>" data-slug="<?php echo $row['slug'] ?>" data-name="<?php echo $row['name'] ?>" data-category="<?php echo $row['area'] ?>">
                                            <img src="assets/img/profiles/<?php echo $row['slug'] ?>.png" alt="">
                                            <h3><?php echo $row['name'] ?></h3>
                                        </li>
                                <?php endwhile;
                                }
                                ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <ul class="ky1-rsm">
            <li>
                <div class="box-img img-5">
                    <img src="assets/img/porcentaje.png" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="porcentHours"></span>
                    <p>Porcentaje</p>
                </div>
            </li>
            <li>
                <div class="box-img img-1">
                    <img src="assets/img/tot.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="totalHours"></span>
                    <p>Total Horas</p>
                </div>
            </li>

            <li>
                <div class="box-img img-2">
                    <img src="assets/img/rgs.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="totalMissingPoints"></span>
                    <p>Sin Registro</p>
                </div>
            </li>
            <li>
                <div class="box-img img-3">
                    <img src="assets/img/trd.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="totalLatePoints"></span>
                    <p>Penalización Acumulada</p>
                </div>
            </li>
            <li>
                <div class="box-img img-4">
                    <img src="assets/img/tol.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="tolerancia"></span>
                    <p>Tolerancia</p>
                </div>
            </li>
            <li>
                <div class="box-img img-6">
                    <img src="assets/img/tarde.png" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="tarde"></span>
                    <p>Tardanzas</p>
                </div>
            </li>
        </ul>
        <ul class="ky1-hrr">
            <li class="hrr-box">
                <span>Semana 1</span>
                <!-- <div class="data-sem">
                    <p class="porT">80%</p>
                    <p class="minS">20:00h</p>
                </div> -->
                <div class="hrr-day">
                    <!-- <ul>
                        <li class="day-nam"></li>
                        <li><img src="assets/img/a.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/b.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/c.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/d.svg" width="20" height="20" alt=""></li>
                    </ul> -->
                    <ul>
                        <li class="day-nam">lun 1</li>
                        <li class="day-trd">09:11</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                        <li class="calc">+08:04</li>
                    </ul>
                    <ul>
                        <li class="day-nam">mar 2</li>
                        <li>09:00</li>
                        <li class="day-rgs">13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                    </ul>
                    <ul>
                        <li class="day-nam">mie 3</li>
                        <li>09:09</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                    </ul>
                    <ul>
                        <li class="day-nam">jue 4</li>
                        <li>09:09</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                    </ul>
                    <ul>
                        <li class="day-nam">vie 5</li>
                        <li>09:09</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                    </ul>
                    <ul>
                        <li class="day-nam">sab 6</li>
                        <li>09:09</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
                    </ul>
                </div>
            </li>
        </ul>

        <?php
        $firstIndex = true;
        $sql = "SELECT id_user, dni FROM Users";
        $result = $conn->query($sql);
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[$row["dni"]] = $row["id_user"];
        }
        $csv = 'final.csv';

        if (($reader = fopen($csv, "r")) !== FALSE) {
            $n = 0;
            $store = false;
            $store_id = 0;
            $start_date_id = 0;

            while (($row = fgetcsv($reader, 1000, ",")) !== FALSE) {
                if ($n == 2) {
                    foreach ($row as $element) {
                        if (strpos($element, "~") !== false) {
                            $start_date = substr($element, 0, 10);

                            $sql = "SELECT id_date FROM Calendar WHERE calendar_date = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $start_date);
                            $stmt->execute();
                            $stmt->bind_result($id_date);
                            if ($stmt->fetch()) {
                                $start_date_id = $id_date;
                            }
                            $stmt->close();
                            break;
                        }
                    }
                } elseif ($n > 3) {
                    if ($store) {
                        $insert_query = "INSERT IGNORE INTO Schedule (id_user, id_calendar, stamp) VALUES ";
                        $offset = 0;
                        foreach ($row as $element) {
                            $date_id = $start_date_id + $offset;
                            if ($element != '') {
                                $split = str_split($element, 5);
                                // echo json_encode($split);
                                // echo "\n";
                                foreach ($split as $id => $value) {
                                    if (isset($split[$id + 1])) {
                                        $current = strtotime($value);
                                        $next = strtotime($split[$id + 1]);
                                        $offset_minutes = ($next - $current) / 60;

                                        if ($offset_minutes <= 6) {
                                            unset($split[$id + 1]);
                                        }
                                    }
                                }
                                $split = array_values($split);
                                $element = implode("", $split);
                                //$element = str_replace(":", "", $element);
                                $insert_query .= "(" . $store_id . ", " . $date_id . ", '" . $element . "'), ";
                            }
                            $offset++;
                        }
                        $insert_query = rtrim($insert_query, ", ");
                        if ($conn->query($insert_query) === TRUE) {
                            //echo "Se insertaron los registros correctamente.";
                        } else {
                            //echo "Error al insertar los registros: " . $conn->error;
                        }
                        $store = false;
                    } else {
                        $full_row = implode(",", $row);
                        foreach ($users as $dni => $id_user) {
                            if (strpos($full_row, $dni) !== false) {
                                $store = true;
                                $store_id = $id_user;
                                break;
                            } else {
                                $n++;
                            }
                        }
                    }
                }
                $n++;
            }
            fclose($reader);
        } else {
            echo "No se pudo abrir el archivo.";
        }
        ?>
    </section>
    <div class="modal-stamp" style="display: none;">
        <div class="modal-content">
            <h1>Actualizar Registro</h1>
            <form id="stampForm" enctype="multipart/form-data">
                <label for="dayInput">Día:</label>
                <input type="text" id="dayInput" name="day" disabled>
                <label for="stampInput" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">Registro:</label>
                <input type="text" id="stampInput" name="stamp" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">
                <input type="hidden" id="dateInput" name="date">
                <input type="hidden" id="userIdInput" name="userId">
                <input type="hidden" id="justNameInput" name="just">
                <label for="justInput">Justificación:</label>
                <input type="file" id="justInput" name="justFile" accept=".jpg, .jpeg, .png, .pdf">
                <label for="comentInput" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">Comentarios:</label>
                <textarea name="coment" id="comentInput" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>"></textarea>
                <input type="submit" value="Guardar">
            </form>
        </div>
    </div>

    <div id="messageVerify" class="message-verify">
        <img src="./assets/img/check.png" alt="">
        <p>Se ha actualizado el registro correctamente</p>
    </div>

    <div class="viewDoc" style="display: none;">
        <img src="" alt="">
        <embed src="" type="application/pdf" />
    </div>

    <div class="comentarios-boss">

        <div class="sup">
            <h1>Comentarios</h1>
            <div class="envio">
                <form action="../../routes/del/insertCommentBoss.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <textarea name="comentario" id="comentarioInput" required></textarea>
                    <input type="submit" value="Agregar">
                </form>
            </div>
        </div>
        <?php
        $query = "SELECT c.comentario, u.name 
        FROM Comentarios c
        JOIN Users u ON c.id_user = u.id_user
        WHERE c.id_user = ?  // Filtrar por el id_user de la sesión
        ORDER BY c.created_at DESC";  // Ordena por la fecha más reciente

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);  // Vincula el parámetro de la sesión al placeholder
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<p><strong>' . htmlspecialchars($row['name']) . ':</strong> ' . htmlspecialchars($row['comentario']) . '</p>';
            }
        } else {
            echo '<p>No hay comentarios disponibles.</p>';
        }

        $stmt->close();
        mysqli_close($conn);
        ?>
    </div>
</body>

</html>