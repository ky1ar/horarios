<?php
require_once '../../includes/app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['fileInput']['name'])) {
        echo "Por favor, selecciona un archivo.";
        exit;
    }
    $targetDir = __DIR__ . '/../../';
    $targetFile = $targetDir . 'final.csv';
    $uploadOk = 1;

    $fileType = pathinfo($_FILES['fileInput']['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'csv') {
        echo "Solo se permiten archivos CSV.";
        $uploadOk = 0;
    }

    if ($_FILES['fileInput']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al subir el archivo.";
        $uploadOk = 0;
    }
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }
    if ($uploadOk) {
        chmod($targetDir, 0777);
        if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $targetFile)) {
            echo "El archivo ha sido subido correctamente.";
            global $conn;
            $sqlInsert = "INSERT INTO Archivos (stamp_date) VALUES (CURRENT_TIMESTAMP)";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "Registro insertado en la tabla Archivos.";
                $lastInsertedId = $conn->insert_id;

            } else {
                echo "Error al insertar el registro: " . $conn->error;
            }
        } else {
            echo "Error al mover el archivo.";
        }
    }
}
setcookie("registro_actualizado", "true", time() + 20, "/");
header('Location: ../../load.php');
exit;
?>
