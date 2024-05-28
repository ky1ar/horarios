<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = __DIR__ . '/';
    $targetFile = $targetDir . 'final.csv';
    $uploadOk = 1;

    // Comprobar si el archivo es un CSV
    $fileType = pathinfo($_FILES['fileInput']['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'csv') {
        echo "Solo se permiten archivos CSV.";
        $uploadOk = 0;
    }

    // Verificar si hay errores al subir el archivo
    if ($_FILES['fileInput']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al subir el archivo.";
        $uploadOk = 0;
    }

    // Mover el archivo cargado a la ubicación de destino y reemplazar si ya existe
    if ($uploadOk) {
        // Establecer los permisos de escritura adecuados
        chmod($targetDir, 0777);

        if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $targetFile)) {
            echo "El archivo ha sido subido correctamente.";
        } else {
            echo "Error al mover el archivo.";
        }
    }
}

// Redirigir de vuelta a la página principal después de cargar
header('Location: ../../load.php');
exit;
?>
