<header id="navigationBar">
    <div class="wrapper">
        <a href="/"><img class="logo" width="150" height="42" src="assets/img/logod.webp" alt="Logo Krear 3D"></a>
        <ul>
        <?php
            $sql = "SELECT * FROM Location ORDER BY name";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()):?>
                <li><a <?php if($currentPage == $row['name']) echo 'class="active"'; ?> href="/<?php echo $row['slug'] ?>"><?php echo $row['name'] ?></a></li>
            <?php 
            endwhile; 
            ?>
            <!--
            <li><a <?php //if($currentPage == 'Conocimiento') echo 'class="active"'; ?> href="#">Conocimiento</a></li>
            <li><a <?php //if($currentPage == 'Cursos') echo 'class="active"'; ?> href="#">Cursos</a></li>
            -->
        </ul>
        <a class="link" href="https://tiendakrear3d.com/">Tienda<img class="hdr-wsp" width="16" height="16" src="assets/img/tnd.svg" alt="ico"></a>
    </div>
</header>