<?php
require_once '../../includes/app/db.php';

// Lista de los meses en español
$mesesEnEspanol = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
];

if (isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];

    $query = "SELECT c.comentario, c.created_at
              FROM Comentarios c
              WHERE c.id_user = ?
              ORDER BY c.created_at DESC";
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
            // Convertir la fecha a formato 'día de mes del año'
            $date = $row['created_at'];
            $timestamp = strtotime($date);
            $day = date('d', $timestamp);
            $month = date('F', $timestamp); // Mes en inglés
            $year = date('Y', $timestamp);
            
            // Convertir el mes al español
            if (array_key_exists($month, $mesesEnEspanol)) {
                $month = $mesesEnEspanol[$month];
            }

            $formatted_date = "$day de $month del $year";

            $comments[] = [
                'comentario' => htmlspecialchars($row['comentario']),
                'created_at' => $formatted_date
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
