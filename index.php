<?php

require_once 'db.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/img/fav.png">
    <title>Krear 3D | Horarios</title>
    <?php require_once 'header.php'; ?>
</head>
<body>
    <aside id="ky1-lft">
        <a href="" class="ky1-lgo"><img src="assets/img/logod.webp" alt=""></a>
        <ul class="ky1-lst">
            <li><img src="assets/img/cal.svg" width="20" height="20" alt="">Horarios</li>
            <li><img src="assets/img/cal.svg" width="20" height="20" alt="">Historial</li>
            <li><img src="assets/img/cal.svg" width="20" height="20" alt="">Anuncios</li>
        </ul>
    </aside>
    <section id="ky1-rgt">
        <header>
            <div class="ky1-ttl">
                <h1>Horarios</h1>
                <span>Registro biométrico del mes</span>
            </div>
            <div class="ky1-dte">
                <img src="assets/img/cal.svg" width="20" height="20" alt="">
                <span>Enero, 2024</span>
                <!--<img src="assets/img/r.svg" alt="">-->
            </div>
            <div class="ky1-usr">
                <div class="usr-btn" id="previousUser">
                    <img src="assets/img/r.svg" width="12" height="12" alt="">
                </div>
                <div id="selectedUser" data-id="19">
                    <img id="userImage" src="assets/img/kenny.png" alt="">
                    <span>
                        <h3 id="userName">Kenny Muñoz</h3>
                        <h4 id="userCategory">Sistemas</h4>
                    </span>
                    <div id="userList">
                        <ul>
                            <?php 
                            $firstIndex = true;
                            $sql = "SELECT u.id_user, u.slug, u.name, a.name as area FROM Users u INNER JOIN Profile p ON u.id_profile = p.id_profile INNER JOIN Area a ON u.id_area = a.id_area ORDER BY u.name";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):?>
                                    <li <?php echo $firstIndex ? 'class="active"':'' ?> data-id="<?php echo $row['id_user'] ?>" data-slug="<?php echo $row['slug'] ?>" data-name="<?php echo $row['name'] ?>" data-category="<?php echo $row['area'] ?>">
                                        <img src="assets/img/<?php echo $row['slug'] ?>.png" alt="">
                                        <h3><?php echo $row['name'] ?></h3>
                                    </li>
                                <?php 
                                $firstIndex = false;
                                endwhile; 
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="usr-btn" id="nextUser">
                    <img src="assets/img/r.svg" width="12" height="12" alt="">
                </div>
            </div>
        </header>
        <ul class="ky1-rsm">
            <li>
                <div class="box-img img-1">
                    <img src="assets/img/tot.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span>42:30 h</span>
                    <p>Total Horas</p>
                </div>
            </li>
            <li>
                <div class="box-img img-2">
                    <img src="assets/img/rgs.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span>4</span>
                    <p>Sin Registro</p>
                </div>
            </li>
            <li>
                <div class="box-img img-3">
                    <img src="assets/img/trd.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span>0</span>
                    <p>Tardanzas</p>
                </div>
            </li>
            <li>
                <div class="box-img img-4">
                    <img src="assets/img/flt.svg" width="40" height="40" alt="">
                </div>
                <div class="box-txt">
                    <span>1</span>
                    <p>Faltas Injustificadas</p>
                </div>
            </li>
        </ul>
        <ul class="ky1-hrr">
            <li class="hrr-box">
                <span>Semana 1</span>
                <div class="hrr-day">
                    <!--<ul>
                        <li class="day-nam"></li>
                        <li><img src="assets/img/a.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/b.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/c.svg" width="20" height="20" alt=""></li>
                        <li><img src="assets/img/d.svg" width="20" height="20" alt=""></li>
                    </ul>-->
                    <ul>
                        <li class="day-nam">lun 1</li>
                        <li class="day-trd">09:11</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
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
            <li class="hrr-box">
                <span>Semana 1</span>
                <div class="hrr-day">
                    <ul>
                        <li class="day-nam">lun 1</li>
                        <li class="day-trd">09:11</li>
                        <li>13:10</li>
                        <li>14:08</li>
                        <li>18:05</li>
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
        while ($fila = $resultado->fetch_assoc()) {
            $users[] = $fila;
        }
        print_r($users)
        $csv = 'final.csv';
        $n = 0;

        if (($reader = fopen($csv, "r")) !== FALSE) {
            while (($row = fgetcsv($reader, 1000, ",")) !== FALSE) {
                if ($n == 2) {  
                    foreach ($row as $element) {
                        if (strpos($element, "~") !== false) {
                            $start_date = substr($element, 0, 10);
                            $end_date = substr($element, -10);

                            $start_time = strtotime($start_date);
                            $end_time = strtotime($end_date);

                            $seconds = $end_time - $start_time;
                            $days = floor($seconds / (60 * 60 * 24));
                            //echo "Inicia el $start_date $end_date y $days son días.";
                        }
                    }
                }
                if ($n > 3) {

                }
                $n++;
            }
            fclose($reader);
        } else {
            echo "No se pudo abrir el archivo.";
        }
        ?>
    </section>
</body>
</html>