<?php
require_once '../../includes/app/db.php';

$mesesEnEspanol = [
    'january' => 'enero',
    'february' => 'febrero',
    'march' => 'marzo',
    'april' => 'abril',
    'may' => 'mayo',
    'june' => 'junio',
    'july' => 'julio',
    'august' => 'agosto',
    'september' => 'septiembre',
    'october' => 'octubre',
    'november' => 'noviembre',
    'december' => 'diciembre'
];

if (isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];

    // Consulta con JOIN para obtener también el nombre del autor (de la tabla Users)
    $query = "SELECT c.comentario, c.created_at, u.name AS autor
              FROM Comentarios c
              JOIN Users u ON c.autor = u.id_user  -- Usamos el campo 'autor' para hacer JOIN con 'Users'
              WHERE c.id_user = ?
              ORDER BY c.created_at DESC
              LIMIT 5";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $date = $row['created_at'];
            $timestamp = strtotime($date);
            $day = date('d', $timestamp);
            $month = strtolower(date('F', $timestamp));
            $year = date('Y', $timestamp);

            if (array_key_exists($month, $mesesEnEspanol)) {
                $month = $mesesEnEspanol[$month];
            }
            $formatted_date = "$day de $month del $year";

            // Añadir el nombre del autor a los comentarios
            $comments[] = [
                'comentario' => htmlspecialchars($row['comentario']),
                'created_at' => $formatted_date,
                'autor' => htmlspecialchars($row['autor']) // Aquí obtenemos el nombre del autor
            ];
        }

        echo json_encode(['success' => true, 'comments' => $comments]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay comentarios disponibles']);
    }

    $stmt->close();
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
}
?>
