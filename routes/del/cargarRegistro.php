<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha seleccionado un archivo
    if (empty($_FILES['fileInput']['name'])) {
        echo "Por favor, selecciona un archivo.";
        exit; // Detener la ejecución del script si no se selecciona ningún archivo
    }

    $targetDir = __DIR__ . '/../../';
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

    // Eliminar el archivo existente (si existe)
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }

    // Mover el archivo cargado a la ubicación de destino
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
setcookie("registro_actualizado", "true", time() + 20, "/"); // La cookie expirará en 20s
// Redirigir de vuelta a la página principal después de cargar
header('Location: ../../load.php');
exit;
?>
