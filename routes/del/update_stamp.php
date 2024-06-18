<?php
require_once '../../includes/app/db.php';

function generateUniqueFileName($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if (isset($_POST['userId']) && isset($_POST['date']) && isset($_POST['stamp']) && isset($_POST['coment'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];
    $stamp = $_POST['stamp'];
    $coment = $_POST['coment'];
    $just = isset($_POST['just']) ? $_POST['just'] : '';
    $isNewRecord = false;

    if (isset($_FILES['justFile']) && $_FILES['justFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['justFile']['tmp_name'];
        $fileName = $_FILES['justFile']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Formato de archivo incorrecto']);
            exit;
        }

        $currentDate = date('Ymd');
        do {
            $newFileName = $currentDate . generateUniqueFileName() . '.' . $fileExtension;
            $checkSql = "SELECT COUNT(*) as count FROM Schedule WHERE just = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $newFileName);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();
            $isUnique = ($checkRow['count'] == 0);
            $checkStmt->close();
        } while (!$isUnique);

        $uploadFileDir = '../../justs/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $just = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
            exit;
        }
    }

    $sql = "SELECT s.id_schedule, s.stamp, s.coment, s.calc_diff
            FROM Schedule s
            JOIN Calendar c ON s.id_calendar = c.id_date
            WHERE s.id_user = ? AND c.calendar_date = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $previousStamp = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idSchedule = $row['id_schedule'];
        $previousStamp = $row['stamp'];

        // Imprimir el stamp anterior por consola
        error_log("Stamp anterior: $previousStamp");

        // Calcular la diferencia de caracteres en el `stamp`
        $previousLength = strlen($previousStamp);
        $newLength = strlen($stamp);
        $difference = $newLength - $previousLength;
        $calcDiff = $row['calc_diff'];

        if ($calcDiff === NULL) {
            $calcDiff = intdiv($difference, 5);
        }

        
        $updateModified = ($row['just'] !== $just || $row['coment'] !== $coment) ? 1 : 0;

        $updateSql = "UPDATE Schedule SET stamp = ?, just = ?, coment = ?, calc_diff = ?, modified = ? WHERE id_schedule = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssiii", $stamp, $just, $coment, $calcDiff, $updateModified, $idSchedule);

        if ($updateStmt->execute()) {
            $isNewRecord = false;
            // Imprimir el nuevo stamp por consola
            error_log("Stamp actualizado: $stamp");
            echo json_encode(['success' => true, 'isNewRecord' => $isNewRecord, 'calcDiff' => $calcDiff]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
        }
        $updateStmt->close();
    } else {
        // Calcular la diferencia de caracteres en el `stamp`
        $previousLength = 0; // No hay stamp anterior en un nuevo registro
        $newLength = strlen($stamp);
        $difference = $newLength - $previousLength;
        $calcDiff = intdiv($difference, 5);

        $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp, just, coment, modified, created_from_form, calc_diff)
                      SELECT ?, c.id_date, ?, ?, ?, 1, 1, ?
                      FROM Calendar c
                      WHERE c.calendar_date = ?";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isssis", $userId, $stamp, $just, $coment, $calcDiff, $date);

        if ($insertStmt->execute()) {
            $isNewRecord = true;
            // Imprimir el nuevo stamp por consola
            error_log("Stamp insertado: $stamp");
            echo json_encode(['success' => true, 'isNewRecord' => $isNewRecord, 'calcDiff' => $calcDiff]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert stamp']);
        }
        $insertStmt->close();
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron los parámetros necesarios.']);
}

$conn->close();
