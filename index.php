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
            <?php 
            $sql = "SELECT * FROM Location ORDER BY name";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()):?>
                <li data-id="<?php echo $row['id_location'] ?>" data-slug="<?php echo $row['slug'] ?>">
                    <img src="assets/img/cal.svg" width="20" height="20" alt=""><?php echo $row['name'] ?>
                </li>
            <?php 
            endwhile; 
            ?>
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
                            $sql = "SELECT u.id_user, u.slug, u.name, a.name as area FROM Users u INNER JOIN Profile p ON u.id_profile = p.id_profile INNER JOIN Area a ON u.id_area = a.id_area WHERE u.id_location = 1 ORDER BY u.name";
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
                <?php 
                $selected_interval = '2024-03-01';
                $days_in_month = date('t', strtotime($selected_interval));
                $day_of_week = date('w', strtotime($selected_interval));
                $start_date = date('Y-m-d', strtotime("-$day_of_week days +1 day", strtotime($selected_interval)));

                $total_days = $days_in_month + $day_of_week;
                $end_date = date('Y-m-d', strtotime("+$total_days days", strtotime($selected_interval)));

                echo "$start_date y $end_date";
               
                $new_date_id = 0;
                $sql = "SELECT id_date FROM Calendar WHERE calendar_date = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $start_date);
                $stmt->execute();
                $stmt->bind_result($id_date);
                if ($stmt->fetch()) {
                    $new_date_id = $id_date;
                }
                $stmt->close();

                $sql = "SELECT s.stamp, c.calendar_date FROM Schedule s INNER JOIN Calendar c ON c.id_date = s.id_calendar WHERE id_user = 2 AND id_calendar >= $new_date_id AND id_calendar <= ($new_date_id + $total_days)";
                $result = $conn->query($sql);
                $result = $result->fetch_all(MYSQLI_ASSOC);
                print_r($result);

                //$day = 1;
                //while ($day <= $total_days) {
                    //$sql = "SELECT s.stamp, c.calendar_date FROM Schedule s INNER JOIN Calendar c ON c.id_date = s.id_calendar WHERE id_user = 2 AND id_calendar >= $new_date_id AND id_calendar <= ($new_date_id + $total_days)";
                    //$day++;
                //}

                $firstIndex = true;
                $sql = "SELECT u.id_user, u.slug, u.name, a.name as area FROM Users u INNER JOIN Profile p ON u.id_profile = p.id_profile INNER JOIN Area a ON u.id_area = a.id_area WHERE u.id_location = 1 ORDER BY u.name";
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
    </section>
</body>
</html>