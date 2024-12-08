<?php
require 'conexionbd.php';

// Verifica si la solicitud fue enviada con los parámetros necesarios
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Obtenemos la fecha actual en formato 'Y-m-d H:i:s'
    $fechaAct = date('Y-m-d');

    // Preparamos la consulta SQL para actualizar la fecha de actualización
    $query = "UPDATE solicitudes_vacaciones SET fecha_act = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        // Vinculamos los parámetros
        $stmt->bind_param("si", $fechaAct, $id);
        
        // Ejecutamos la consulta
        if ($stmt->execute()) {
            echo "success";  // Respuesta de éxito
        } else {
            echo "error";    // Respuesta de error
        }

        // Cerramos la declaración
        $stmt->close();
    } else {
        echo "error";  // Error en la preparación de la consulta
    }

    // Cerramos la conexión
    $conn->close();
} else {
    echo "error";  // Si no se recibe el ID
}
?>