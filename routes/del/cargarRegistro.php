<?php
session_start();

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = __DIR__ . '/../../';
    $targetFile = $targetDir . 'final.csv';
    $uploadOk = 1;

    // Comprobar si el archivo es un CSV
    $fileType = pathinfo($_FILES['fileInput']['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'csv') {
        $_SESSION['uploadMessage'] = "Solo se permiten archivos CSV.";
        $uploadOk = 0;
    }

    // Verificar si hay errores al subir el archivo
    if ($_FILES['fileInput']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['uploadMessage'] = "Error al subir el archivo.";
        $uploadOk = 0;
    }

    // Eliminar el archivo existente (si existe)
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }

    // Mover el archivo cargado a la ubicación de destino
    if ($uploadOk) {
        // Establecer los permisos de escritura adecuados
        chmod($targetDir, 0777);

        if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $targetFile)) {
            $_SESSION['uploadSuccess'] = true; // Indicar que el archivo se ha cargado correctamente
        } else {
            $_SESSION['uploadMessage'] = "Error al mover el archivo.";
        }
    }

    // Redirigir de vuelta a la página principal después de cargar
    header('Location: ../../load.php');
    exit;
}
?>
