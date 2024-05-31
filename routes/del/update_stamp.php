<?php
require_once '../../includes/app/db.php';

// if (isset($_POST['userId']) && isset($_POST['date']) && isset($_POST['stamp'])) {
//     $userId = $_POST['userId'];
//     $date = $_POST['date'];
//     $stamp = $_POST['stamp'];
//     $sql = "SELECT s.id_schedule
//             FROM Schedule s
//             JOIN Calendar c ON s.id_calendar = c.id_date
//             WHERE s.id_user = ? AND c.calendar_date = ?";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("is", $userId, $date);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows > 0) {
//         $row = $result->fetch_assoc();
//         $idSchedule = $row['id_schedule'];

//         $updateSql = "UPDATE Schedule SET stamp = ? WHERE id_schedule = ?";
//         $updateStmt = $conn->prepare($updateSql);
//         $updateStmt->bind_param("si", $stamp, $idSchedule);

//         if ($updateStmt->execute()) {
//             echo json_encode(['success' => true]);
//         } else {
//             echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
//         }
//         $updateStmt->close();
//     } else {
//         $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp)
//                       SELECT ?, c.id_date, ?
//                       FROM Calendar c
//                       WHERE c.calendar_date = ?";
//         $insertStmt = $conn->prepare($insertSql);
//         $insertStmt->bind_param("iss", $userId, $stamp, $date);

//         if ($insertStmt->execute()) {
//             echo json_encode(['success' => true]);
//         } else {
//             echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
//         }
//         $insertStmt->close();
//     }
//     $stmt->close();
// } else {
//     echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
// }


// Verificar si se recibieron los parámetros necesarios
if (isset($_POST['userId']) && isset($_POST['date']) && isset($_POST['stamp'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];
    $stamp = $_POST['stamp'];

    // Verificar si se ha cargado un archivo
    if (isset($_FILES['justInput']) && $_FILES['justInput']['error'] === UPLOAD_ERR_OK) {
        // Obtener información del archivo
        $fileName = $_FILES['justInput']['name'];
        $fileTmpName = $_FILES['justInput']['tmp_name'];
        $fileSize = $_FILES['justInput']['size'];
        $fileType = $_FILES['justInput']['type'];

        // Mover el archivo cargado a la ubicación deseada
        $targetDir = __DIR__ . '/../../justs/'; // Directorio donde se guardarán las imágenes
        $targetFile = $targetDir . basename($fileName);

        // Guardar el nombre del archivo en la base de datos
        $just = $fileName;

        // Consulta para verificar si ya existe un registro para el usuario y la fecha
        $sql = "SELECT s.id_schedule
                FROM Schedule s
                JOIN Calendar c ON s.id_calendar = c.id_date
                WHERE s.id_user = ? AND c.calendar_date = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Actualizar el stamp existente
            $row = $result->fetch_assoc();
            $idSchedule = $row['id_schedule'];

            $updateSql = "UPDATE Schedule SET stamp = ?, just = ? WHERE id_schedule = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $stamp, $just, $idSchedule);

            if ($updateStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
            }
            $updateStmt->close();
        } else {
            // Insertar un nuevo registro de stamp
            $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp, just)
                          SELECT ?, c.id_date, ?, ?
                          FROM Calendar c
                          WHERE c.calendar_date = ?";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("isss", $userId, $stamp, $just, $date);

            if ($insertStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
            }
            $insertStmt->close();
        }
        $stmt->close();
    } else {
        // No se ha seleccionado un archivo, solo actualizar el sello (stamp)
        $sql = "SELECT s.id_schedule
                FROM Schedule s
                JOIN Calendar c ON s.id_calendar = c.id_date
                WHERE s.id_user = ? AND c.calendar_date = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Actualizar el stamp existente
            $row = $result->fetch_assoc();
            $idSchedule = $row['id_schedule'];

            $updateSql = "UPDATE Schedule SET stamp = ? WHERE id_schedule = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $stamp, $idSchedule);

            if ($updateStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
            }
            $updateStmt->close();
        } else {
            // Insertar un nuevo registro de stamp
            $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp)
                          SELECT ?, c.id_date, ?
                          FROM Calendar c
                          WHERE c.calendar_date = ?";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iss", $userId, $stamp, $date);

            if ($insertStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}
$conn->close();
?>