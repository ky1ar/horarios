<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once './includes/app/db.php';
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
    <div class="out" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">
        <a href="./routes/del/logout.php"><img src="./assets/img/out.svg" alt=""></a>
    </div>
    <div class="cont-insert" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">
        <form id="uploadForm" action="./routes/del/cargarRegistro.php" method="post" enctype="multipart/form-data" class="form-insert">
            <label for="fileInput" class="custom-file-upload insert">Archivo</label>
            <input type="file" id="fileInput" name="fileInput" accept=".csv" style="display: none;" required>
            <input type="submit" value="Cargar">
        </form>
    </div>
    <section id="ky1-rgt">
        <header>
            <div class="ky1-ttl">
                <h1>Horarios Krear 3D</h1>
                <span>Registro biométrico del mes</span>
            </div>
            <div class="ky1-permisos" style="display: <?php echo ($rango == 1) ? 'none' : 'flex'; ?>">
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
                <div id="indice">
                    <h1>Leyenda</h1>
                    <p><span></span> Normal</p>
                    <p><span></span> Modificado</p>
                    <p><span></span> Permiso de Salud</p>
                    <p><span></span> Servicio</p>
                    <p><span></span> Vacaciones</p>
                </div>
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
                                $sql = "SELECT u.id_user, u.slug, u.name, a.name as area FROM Users u INNER JOIN Profile p ON u.id_profile = p.id_profile INNER JOIN Area a ON u.id_area = a.id_area WHERE u.id_user NOT IN (20, 17, 24) ORDER BY u.name";
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
            <li>
                <div class="box-img img-7">
                    <img src="assets/img/vacaciones.png" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span id="vac"></span>
                    <p>Vacaciones</p>
                </div>
            </li>
        </ul>
        <ul class="ky1-hrr">
            <li class="hrr-box">
                <span>Semana 1</span>
                <div class="hrr-day">
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
                                $insert_query .= "(" . $store_id . ", " . $date_id . ", '" . $element . "'), ";
                            }
                            $offset++;
                        }
                        $insert_query = rtrim($insert_query, ", ");
                        if ($conn->query($insert_query) === TRUE) {
                        } else {
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
                <div class="checks" id="fast-access">
                    <label class="switch">
                        <input type="checkbox" id="check1">
                        <span class="slider round"></span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" id="check2">
                        <span class="slider round"></span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" id="check3">
                        <span class="slider round"></span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" id="check4">
                        <span class="slider round"></span>
                    </label>
                </div>
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
    <div class="viewDoc" style="display: none;">
        <img src="" alt="">
        <embed src="" type="application/pdf" />
    </div>

    <div class="comentarios-boss" id="comments-container">
        <h1>Notificaciones</h1>
        <div class="envio" style="display: <?php echo ($rango == 1) ? 'flex' : 'none'; ?>">
            <form id="commentForm">
                <textarea id="commentb"></textarea>
                <input type="submit" value="Comentar">
            </form>
        </div>
        <div id="mensajes" class="mensajes">

        </div>
    </div>
    </div>
</body>

</html>