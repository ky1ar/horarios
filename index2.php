<?php 
$currentPage = "Miraflores"; 
require_once 'includes/app/db.php';
require_once 'includes/app/globals.php'; 
require_once 'includes/common/header.php';
?>
</head>
<body>
    <?php 
    require_once 'includes/bar/topBar.php';
    require_once 'includes/bar/navigationBar.php';  
    ?>
    <section id="patternBody">
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
        <section id="timeline">
            <div class="schedule">
                <span class="name">Hora</span>
                <ul class="lines">
                    <li>08:00</li>
                    <li class="hightlight">09:00</li>
                    <li>10:00</li>
                    <li>11:00</li>
                    <li>12:00</li>
                    <li class="hightlight">13:00</li>
                    <li class="hightlight">14:00</li>
                    <li>15:00</li>
                    <li>16:00</li>
                    <li>17:00</li>
                    <li class="hightlight">18:00</li>
                    <li>19:00</li>
                </ul>
            </div>
            <?php 
            $selected_interval = '2024-03-01';
            $day_names = ['lun','mar','mié','jue','vie','sáb','dom'];
            $total_days = date('t', strtotime($selected_interval));
            $day_of_week = date('w', strtotime($selected_interval));
            $start_date = date('Y-m-d', strtotime("-$day_of_week days +1 day", strtotime($selected_interval)));
            $total_days = $total_days + $day_of_week - 2;

            $start_date_id = 0;
            $sql = "SELECT id_date FROM Calendar WHERE calendar_date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $start_date);
            $stmt->execute();
            $stmt->bind_result($id_date);
            if ($stmt->fetch()) {
                $start_date_id = $id_date;
            }
            $stmt->close();

            

            $sql = "SELECT s.id_schedule, c.id_date, c.calendar_date, s.stamp, s.id_user FROM Calendar c LEFT JOIN Schedule s ON s.id_calendar = c.id_date AND s.id_user = 18 WHERE c.id_date BETWEEN $start_date_id AND ($start_date_id + $total_days);";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()){
                $day_week = date('w', strtotime($row['calendar_date']));
                $day = ltrim(date('d', strtotime($row['calendar_date'])), '0');

                if ($day_week == 1){
                    
                } 
                if ($row['stamp']  ) {
                    echo
                    "<div class='schedule'>
                        <span class='name'><b>$day</b>".$day_names[$day_week-1]."</span>
                        <div class='container'>";
                        if ($row['stamp']){
                            $array = str_split(trim($row['stamp']), 5);
                            $count = count($array);
                            $start = '08:00';
                            $margin = '0';
                            for ($i = 0; $i < $count; $i++) {
                                if( $i == 0 ) {
                                    $open = intval(substr($start, 0, 2)) * 60 + intval(substr($start, 3));
                                    $close = intval(substr($array[0], 0, 2)) * 60 + intval(substr($array[0], 3));
                                    $total = $close - $open;
                                    $margin = $total/60*40;
                                }
                                if ($i != $count-1) {
                                    $open = intval(substr($array[$i], 0, 2)) * 60 + intval(substr($array[$i], 3));
                                    $close = intval(substr($array[$i+1], 0, 2)) * 60 + intval(substr($array[$i+1], 3));
                                    $total = $close - $open;
                                    $height = $total/60*40;
                                } else {
                                    $height = '0';
                                }
                                echo "<div class='block' style='height: ".$height."px; margin-top: ".$margin."px;'><span>".$array[$i]."</span></div>";
                                $margin = '0';
                            }
                        }
                        echo
                        "</div>
                    </div>";
                }
                if ($day_week == 7){
                    
                }
            }
            ?>
        </section>
    </section>
   
    <?php require_once 'includes/common/footer.php'; ?>
</body>
</html>