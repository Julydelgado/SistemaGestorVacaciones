<?php
require 'conexionbd.php';
require 'verificar_sesion.php';

// Consulta SQL para seleccionar todos los registros de la tabla
$query = "SELECT * FROM empleados";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="css/mostrardatos.css">
    <!-- Agregamos el CSS de SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    
<header>
    <h2 class="logo">
    <?php
     if (isset($_SESSION["nombre"])) {
        echo $_SESSION["nombre"];
      } else {
        echo "Administrador"; // Otra información o mensaje que desees mostrar
      }
    ?>
    </h2>
    <nav class="navigation">
        
        <a href="solicitudesVacaciones_administrador.php">Solicitudes de Vacacaciones</a>
        <button class="btnLogin-poput" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
    </nav>
</header>

<div class="wrapper" style="padding-top:25px">
  <table class="table">
    <div class="centrar-enlace"  style= "display: flex; justify-content: center; margin: 20px 0 20px 0;">
      <h2 style = "margin-left: 400px">Empleados</h2>
      <div class="navigation" > <a href="añadirEmpleados.php" style="margin: 0 10px 0 350px;color:black; background-color: rgb(64, 168, 64); border-radius: 10px; padding: 5px;" >+ Añadir Empleados</a></div>
    </div>
      
    </div>
    <thead class="thead-dark">
    <tr>
        <th scope="col">Nombre</th>
        <th scope="col">Apellido</th>
        <th scope="col">DNI</th>
        <th scope="col">Años en la Empresa</th>
        <th scope="col">Teléfono</th>
        <th scope="col">Nacionalidad</th>
        <th scope="col">Localidad</th>
        <th scope="col">Acciones</th>
        <th scope="col">Vacaciones</th>
        <th scope="col">Acción</th>
    </tr>
</thead>
<tbody>
<?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='data-row' data-id='" . $row["id"] . "'>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["lastname"] . "</td>";
            echo "<td>" . $row["dni"] . "</td>";
            echo "<td>" . $row["anios_ingreso"] . "</td>";
            echo "<td>" . $row["telefono"] . "</td>";
            echo "<td>" . $row["nacionalidad"] . "</td>";
            echo "<td>" . $row["localidad"] . "</td>";
            // Modificamos los enlaces para editar y eliminar
            echo "<td><a href='editar_empleados.php?id=" . $row["id"] . "'><ion-icon name='create' class='btn-modificar'></ion-icon></a> 
               <a href='javascript:;' onclick='confirmarEliminarEmpleado(" . $row["id"] . ")'><ion-icon name='close-circle' class='btn-eliminar'></ion-icon></a></td>";
            echo "<td><a href='asignarvacaciones_administrador.php?id=" . $row["id"] . "' class='btn btn-primary'>Asignar Vacaciones</a></td>";
            echo "<td><a href='mostrarvacaciones_administrador.php?id=" . $row["id"] . "' class='btn btn-primary'>Ver Vacaciones</a></td>";
            echo "</tr>";
        }
    } else {     
      echo "No se encontraron datos.";
    }
?>
</tbody>
</table>
</div>
<!-- Agregamos el script de SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<!-- Agregamos el script para confirmar la eliminación -->
<script>
    function confirmarEliminarEmpleado(empleadoId) {
        // Utilizamos SweetAlert2 para mostrar un cuadro de diálogo de confirmación
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará al empleado y todas sus solicitudes de vacaciones. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, redirigimos a la página de eliminación
                window.location.href = 'eliminar_empleados.php?id=' + empleadoId;
            }
        });
    }
</script>

<?php
if (isset($_GET["message"])) {
    $message = $_GET["message"];
    if ($message === "success") {
        echo '<script>
            Swal.fire({
                icon: "success",
                title: "Éxito",
                text: "El empleado se ha eliminado completamente del sistema con éxtio.",
                showConfirmButton: false,
                timer: 2000
            });
        </script>';
    } elseif ($message === "error") {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ha ocurrido un error al eliminar el empleado.",
                showConfirmButton: false,
                timer: 2000
            });
        </script>';
    }
}
?>

</body>
</html>
