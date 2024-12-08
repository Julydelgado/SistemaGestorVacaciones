<?php
require 'conexionbd.php';
require 'verificar_sesion.php';

// Variables para almacenar los nuevos términos de búsqueda
$estadoBuscado = '';
$idBuscado = '';
$nombreBuscado = '';
$fechaInicioBuscada = '';

// Consulta SQL con placeholders para prevenir inyección SQL
$query = "SELECT sv.id, sv.empleado_id, sv.days, sv.fecha_inicio, sv.fecha_end, sv.fecha_act, sv.estado, e.name, e.lastname, e.dni 
          FROM solicitudes_vacaciones sv
          LEFT JOIN empleados e ON sv.empleado_id = e.id WHERE 1=1";

$params = [];
$types = '';

// Verificar si se han enviado los términos de búsqueda
if (isset($_GET['estado'])) {
    $estadoBuscado = $_GET['estado'];
}

if (isset($_GET['id'])) {
    $idBuscado = $_GET['id'];
}

if (isset($_GET['nombre'])) {
    $nombreBuscado = $_GET['nombre'];
}

if (isset($_GET['fecha_inicio'])) {
    
    
    //if (isset($_GET['fecha_inicio'])) {
        //echo "Fecha inicio buscada: " . $_GET['fecha_inicio'];
    //} else {
        //echo "No se recibió fecha de inicio.";
    //}
    $query .= " AND sv.fecha_inicio >= ?";
    $params[] = $fechaInicioBuscada;
    $types .= 's';  // 's' para string (fecha en formato string)
}




// Condicionalmente agregar filtros
if (!empty($estadoBuscado)) {
    $query .= " AND sv.estado LIKE ?";
    $params[] = "%$estadoBuscado%";
    $types .= 's';  // 's' significa string
}

if (!empty($idBuscado)) {
    $query .= " AND (sv.id LIKE ?)";
    $params[] = "%$idBuscado%";
    $types .= 's';
}

if (!empty($nombreBuscado)) {
    $query .= " AND (e.name LIKE ? OR e.lastname LIKE ?)";
    $params[] = "%$nombreBuscado%";
    $params[] = "%$nombreBuscado%";
    $types .= 'ss';  // 'ss' significa dos strings
}

if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
    // Asignar el valor de la fecha y asegurarse de que esté en formato adecuado
    $fechaInicioBuscada = $_GET['fecha_inicio'];

    // Si es necesario, podemos validar el formato de la fecha para asegurarnos de que es correcto
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fechaInicioBuscada);

    if ($fechaValida && $fechaValida->format('Y-m-d') === $fechaInicioBuscada) {
        // La fecha es válida
        $query .= " AND sv.fecha_inicio = ?";
        $params[] = $fechaInicioBuscada;
        $types .= 's';  // 's' para string, aunque la base de datos la tratará como fecha
    } else {
        // La fecha no es válida
        echo "La fecha no es válida.";
    }
}




// Preparar la consulta
$stmt = $conn->prepare($query);

// Bind los parámetros si existen
if ($params) {
    $stmt->bind_param($types, ...$params);
}

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar Vacaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="css/mostrardatos.css">

    <style>
        body {
            background-color: #f7f7f7;
        }

        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .table {
            background-color: #ffffff;
        }

        .table th {
            background-color: #343a40;
            color: #ffffff;
        }

        .data-row {
            background-color: #f2f2f2;
        }

        /* Estilo para el estado "Pendiente" */
        .estado-pendiente {
            background-color: #ffc107; /* Amarillo */
        }

        /* Estilo para el estado "Aprobado" */
        .estado-aprobado {
            background-color: #28a745; /* Verde */
        }

        /* Estilo para el estado "Rechazado" */
        .estado-rechazado {
            background-color: #dc3545; /* Rojo */
        }
    </style>
</head>
<body>
    
<header>
    <h2 class="logo">
    <?php
        if (isset($_SESSION["nombre"])) {
            echo $_SESSION["nombre"];
        } else {
            echo "Administrador";
        }
    ?>
    </h2>
    <nav class="navigation">
        <a href="mostrardatos.php">Datos Empleados</a>
        <button class="btnLogin-poput" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
    </nav>
</header>

<div class="container mt-5">
    <h2>Solicitudes de Vacaciones</h2>

    <!--<div class="float-right mb-3">
        <form method="get" action="">
            <div class="input-group">
                <input type="text" name="estado" id="filtroEstado" class="form-control" placeholder="Escriba Pendiente, Aprobado o Rechazado" value="<?= $estadoBuscado ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>
    </div>-->
    <div class="float-right mb-3">
        <form method="get" action="" style="display:flex; flex-direction:row;">
        <!-- Filtro de Estado -->
        <div class="input-group mb-2">
            <select name="estado" id="filtroEstado" class="form-control">
                <option value="">Seleccionar Estado</option>
                <option value="Pendiente" <?= $estadoBuscado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Aprobado" <?= $estadoBuscado == 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                <option value="Rechazado" <?= $estadoBuscado == 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
            </select>
        </div>
        <div class="input-group mb-2">
            <input type="number" name="id" id="filtroId" class="form-control" placeholder="ID de Solicitud" value="<?= isset($_GET['id']) ? $_GET['id'] : '' ?>">
        </div>
        <div class="input-group mb-2">
            <input type="text" name="nombre" id="filtroNombre" class="form-control" placeholder="Nombre o Apellido" value="<?= isset($_GET['nombre']) ? $_GET['nombre'] : '' ?>">
        </div>
        <div class="input-group mb-2">
            <input type="date" name="fecha_inicio" id="filtroFechaInicio" class="form-control" placeholder="Fecha de inicio" value="<?= isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '' ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Buscar</button>
        <!-- Botón Limpiar Filtros -->
        <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">Limpiar Filtros</button>

        <script>
        function limpiarFiltros() {
            // Redirige a la misma página sin parámetros en la URL
            window.location.href = window.location.pathname;
        }
        </script>
    </form>

    </div>

    <?php
    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Nombre</th>";
        echo "<th>Apellido</th>";
        echo "<th>DNI</th>";
        echo "<th>Días de Vacaciones</th>";
        echo "<th>Fecha de Inicio</th>";
        echo "<th>Fecha de Fin</th>";
        echo "<th>Fecha de Actualización</th>";
        echo "<th>Estado</th>";
        echo "<th>Acciones</th>";
        echo "<th>Aprobar</th>";
        echo "<th>Rechazar</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
        // Define el color de fondo según el estado
            $backgroundColor = '';
            switch ($row["estado"]) {
                case "Pendiente":
                    $backgroundColor = 'yellow';
                    $textColor = 'black';
                    break;
                case "Rechazado":
                    $backgroundColor = 'red';
                    $textColor = 'white'; 
                    break;
                case "Aprobado":
                    $backgroundColor = 'green';
                    $textColor = 'white';
                    break;
                default:
                    $backgroundColor = '';
            }
            echo "<tr id='tablaVacaciones'>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["lastname"] . "</td>";
            echo "<td>" . $row["dni"] . "</td>";
            echo "<td>" . $row["days"] . "</td>";
            echo "<td>" . $row["fecha_inicio"] . "</td>";
            echo "<td>" . $row["fecha_end"] . "</td>";
            echo "<td>" . $row["fecha_act"] . "</td>";
            // Aplica el color de fondo
            echo "<td id='estado-" . $row["id"] . "' style='background-color: $backgroundColor; color: $textColor;'>" . $row["estado"] . "</td>";
            echo "<td>
                <a href='editarvacaciones.php?id=" . $row["id"] . "'><ion-icon name='create' class='btn-modificar'></ion-icon></a> 
                <a href='javascript:;' onclick='confirmarEliminarVacacion(" . $row["id"] . ")'><ion-icon name='close-circle' class='btn-eliminar'></ion-icon></a>
                </td>";

            // Mostrar el botón Aprobar si el estado es "Pendiente"
            if ($row["estado"] == "Pendiente") {
                echo "<td>    
                    <button onclick='aprobarVacacion(" . $row["id"] . ")' class='btn btn-success' id='botonAprobar-" . $row["id"] . "'>Aprobar</button>
                </td>";
                echo "<td>    
                    <button onclick='rechazarVacacion(" . $row["id"] . ")' class='btn btn-danger' id='botonRechazar-" . $row["id"] . "'>Rechazar</button>
                </td>";
            }
            // Mostrar el botón Aprobar si el estado es "Rechazado"
            elseif ($row["estado"] == "Rechazado") {
                echo "<td>    
                    <button onclick='aprobarVacacion(" . $row["id"] . ")' class='btn btn-success' id='botonAprobar-" . $row["id"] . "'>Aprobar</button>
                </td>";
                echo "<td>    
                    
                </td>";
            } 
            // Mostrar el botón Rechazar si el estado es "Aprobado"
            elseif ($row["estado"] == "Aprobado") {
                echo "<td>    
                    
                </td>";
                echo "<td>    
                    <button onclick='rechazarVacacion(" . $row["id"] . ")' class='btn btn-danger' id='botonRechazar-" . $row["id"] . "'>Rechazar</button>
                </td>";
                
            }

            
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        } else {
        echo "<div class='alert alert-warning' role='alert'>";
        echo "No hay solicitudes de vacaciones en este momento. Si tienes algún problema, comunícate con algún administrador.";
        echo "</div>";
    }
    ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    function confirmarEliminarVacacion(vacacionId) {
        // Utiliza SweetAlert2 para mostrar un cuadro de diálogo de confirmación
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la solicitud de vacaciones. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, llama a la función para eliminar la solicitud
                eliminarVacacion(vacacionId);
            }
        });
    }

    function eliminarVacacion(vacacionId) {
        // Utiliza AJAX para eliminar la solicitud de vacaciones
        $.ajax({
            type: "GET",  // Cambia a GET
            url: "eliminarVacaciones.php?id=" + vacacionId,  // Envía la ID en la URL
            success: function(response) {
                if (response === "success") {
                    // Eliminación exitosa
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'La Solicitud de vacaciones fue eliminada correctamente.',
                        icon: 'success'
                    }).then((result) => {
                        // Recarga la página actual para reflejar los cambios
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al eliminar la solicitud de vacaciones.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function aprobarVacacion(vacacionId) {
        // Supongamos que la solicitud se aprobó con éxito y el estado ahora es "Aprobado"
        var nuevoEstado = "Aprobado";
        actualizarEstado(vacacionId, nuevoEstado);

        // Llamamos a la función para actualizar la fecha
        actualizarFecha(vacacionId);

        // Oculta el botón "Aprobar" y deja visible "Rechazar"
        document.getElementById("botonAprobar-" + vacacionId).style.display = "none";
        document.getElementById("botonRechazar-" + vacacionId).style.display = "inline-block";
    }

    function rechazarVacacion(vacacionId) {
        // Supongamos que la solicitud se rechazó y el estado ahora es "Rechazado"
        var nuevoEstado = "Rechazado";
        actualizarEstado(vacacionId, nuevoEstado);

        // Llamamos a la función para actualizar la fecha
        actualizarFecha(vacacionId);

        // Oculta el botón "Rechazar" y deja visible "Aprobar"
        document.getElementById("botonRechazar-" + vacacionId).style.display = "none";
        document.getElementById("botonAprobar-" + vacacionId).style.display = "inline-block";
    }

    function actualizarFecha(vacacionId) {
    // Realizamos una llamada AJAX para actualizar la fecha de actualización
    $.ajax({
        type: "POST",
        url: "actualizar_fechaActualizacion.php", // Archivo PHP para actualizar la fecha
        data: { id: vacacionId },
        success: function(response) {
            if (response === "success") {
                // Actualización exitosa
                Swal.fire({
                    title: 'Actualizado',
                    text: 'La fecha de actualización ha sido modificada correctamente.',
                    icon: 'success'
                }).then(() => {
                    // Recarga la página para reflejar el cambio
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al actualizar la fecha de actualización.',
                    icon: 'error'
                });
            }
        }
    });
}


    function actualizarEstado(vacacionId, nuevoEstado) {
        // Aquí puedes escribir el código para actualizar el estado en la base de datos utilizando AJAX
        // Debes enviar una solicitud al servidor para actualizar el estado de la solicitud de vacaciones con el nuevoEstado.
        
        // Ejemplo de código AJAX (Debes adaptarlo a tu entorno y base de datos):
        
        $.ajax({
            type: "POST",
            url: "actualizar_estado.php", // Archivo PHP para actualizar el estado en la base de datos
            data: {
                id: vacacionId,
                estado: nuevoEstado
            },
            success: function(response) {
                if (response === "success") {
                    // Actualización exitosa
                    Swal.fire({
                        title: 'Actualizado',
                        text: 'Solicitud de vacaciones ' + nuevoEstado + ' para ID: ' + vacacionId,
                        icon: 'success'
                    }).then((result) => {
                        // Actualiza el texto del estado en la tabla
                        var estadoElement = document.getElementById("estado-" + vacacionId);
                        if (estadoElement) {
                            estadoElement.textContent = nuevoEstado;
                        }
                        // No redirigir, solo actualizar la página actual
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al actualizar el estado.',
                        icon: 'error'
                    });
                }
            }
        });
    }
</script>
</body>
</html>
