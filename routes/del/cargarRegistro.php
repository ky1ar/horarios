<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Aquí, después de completar la carga del archivo, se imprimirá el script JavaScript
            echo '<script>
                document.addEventListener("DOMContentLoaded", function () {
                    var messageVerify = document.querySelector(".message-verify");
                    messageVerify.style.display = "flex";
                    setTimeout(function () {
                        messageVerify.style.display = "none";
                    }, 2000); 
                });
            </script>';
        } else {
            echo "Error al mover el archivo.";
        }
    }
}
?>