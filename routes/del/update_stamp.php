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

if (isset($_POST['userId']) && isset($_POST['date']) && isset($_POST['stamp'])) {
    $userId = $_POST['userId'];
    $date = $_POST['date'];
    $stamp = $_POST['stamp'];
    $just = isset($_POST['just']) ? $_POST['just'] : '';

    if (isset($_FILES['justFile']) && $_FILES['justFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['justFile']['tmp_name'];
        $fileName = $_FILES['justFile']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Formato de archivo incorrecto']);
            exit;
        }

        do {
            $newFileName = generateUniqueFileName() . '.' . $fileExtension;
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

    $sql = "SELECT s.id_schedule
            FROM Schedule s
            JOIN Calendar c ON s.id_calendar = c.id_date
            WHERE s.id_user = ? AND c.calendar_date = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idSchedule = $row['id_schedule'];

        $updateSql = "UPDATE Schedule SET stamp = ?, just = ? WHERE id_schedule = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $stamp, $just, $idSchedule);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'reload' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
        }
        $updateStmt->close();
    } else {
        $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp, just)
                      SELECT ?, c.id_date, ?, ?
                      FROM Calendar c
                      WHERE c.calendar_date = ?";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isss", $userId, $stamp, $just, $date);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'reload' => true]);
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
?>
