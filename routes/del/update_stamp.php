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
    $mid_time = isset($_POST['mid_time']) ? $_POST['mid_time'] : 0; 
    $full_time = isset($_POST['full_time']) ? $_POST['full_time'] : 0; 
    $just = isset($_POST['just']) ? $_POST['just'] : '';
    $isNewRecord = false;
    if ($full_time == 1) {
        $stamp = '09:0013:0014:0018:00';
    } else {
        $stamp = $_POST['stamp'];
    }

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

    $sql = "SELECT s.id_schedule, s.stamp, s.coment, s.calc_diff, s.mid_time, s.full_time
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
        error_log("Stamp anterior: $previousStamp");
        $previousLength = strlen($previousStamp);
        $newLength = strlen($stamp);
        $difference = $newLength - $previousLength;
        $calcDiff = $row['calc_diff'];

        if ($calcDiff === NULL || $calcDiff === 0) {
            $calcDiff = intdiv($difference, 5);
        }

        $updateSql = "UPDATE Schedule SET stamp = ?, just = ?, coment = ?, mid_time = ?, full_time = ?, modified = 1, calc_diff = ? WHERE id_schedule = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssiiii", $stamp, $just, $coment, $mid_time, $full_time, $calcDiff, $idSchedule);

        if ($updateStmt->execute()) {
            $isNewRecord = false;
            error_log("Stamp actualizado: $stamp");
            setcookie('lastUpdatedUserId', $userId, time() + 600, '/');
            echo json_encode(['success' => true, 'isNewRecord' => $isNewRecord, 'calcDiff' => $calcDiff]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stamp']);
        }
        $updateStmt->close();
    } else {
        $previousLength = 0;
        $newLength = strlen($stamp);
        $difference = $newLength - $previousLength;
        $calcDiff = intdiv($difference, 5);

        $insertSql = "INSERT INTO Schedule (id_user, id_calendar, stamp, just, coment, mid_time, full_time, modified, created_from_form, calc_diff)
                      SELECT ?, c.id_date, ?, ?, ?, ?, ?, 1, 1, ?
                      FROM Calendar c
                      WHERE c.calendar_date = ?";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isssiiis", $userId, $stamp, $just, $coment, $mid_time, $full_time, $calcDiff, $date);

        if ($insertStmt->execute()) {
            $isNewRecord = true;
            error_log("Stamp insertado: $stamp");
            setcookie('lastUpdatedUserId', $userId, time() + 600, '/');
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
