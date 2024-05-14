<?php

$servername = "localhost";
$username = "u809802095_horarios";
$password = "";
$database = "u809802095_horarios";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
