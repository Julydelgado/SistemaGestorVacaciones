<?php
require 'conexionbd.php';
require 'verificar_sesion.php';

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    if (isset($_POST["update"])) {
        $name = $_POST["name"];
        $lastname = $_POST["lastname"];
        $dni = $_POST["dni"];
        $anios_ingreso = $_POST["anios_ingreso"];
        $telefono = $_POST["telefono"]; 
        $nacionalidad = $_POST["nacionalidad"]; 
        $localidad = $_POST["localidad"]; 

        // Consulta SQL para verificar si el nuevo DNI ya existe
        $checkQuery = "SELECT * FROM empleados WHERE dni = ? AND id <> ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $dni, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo "<script>alert('El nuevo DNI ya existe en la base de datos.');</script>";
        } else {
            // Consulta SQL para actualizar los datos
            $updateQuery = "UPDATE empleados SET name=?, lastname=?, dni=?, anios_ingreso=?, telefono=?, nacionalidad=?, localidad=? WHERE id=?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("sssiissi", $name, $lastname, $dni, $anios_ingreso, $telefono, $nacionalidad, $localidad, $id);

            if ($updateStmt->execute()) {
                header("Location: mostrardatos.php");
                echo "<script>alert('Los datos se actualizaron correctamente.'); window.location.href='tabla.php';</script>";
            } else {
                echo "<script>alert('Error al actualizar los datos: " . $conn->error . "');</script>";
            }
        }
    }

    // Consulta SQL para obtener los datos actuales del registro
    $query = "SELECT * FROM empleados WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        echo "No se encontró un registro con el ID especificado.";
    }
    } else {
    echo "ID no especificado.";
    }   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="css/editar_empleados.css">
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
        <a href="mostrardatos.php">Volver</a>
        <button class="btnLogin-poput" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
    </nav>
</header>

    <div class="wrapper">
        <div class="form-box register">
            <h1>Editar Empleado</h1>
            <form method="post">
                <div class="input-box">
                     <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="name" id="name" value="<?php echo $row["name"]; ?>" required>
                    <label for="name">Nombre</label>
                </div>

                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="lastname" id="lastname" value="<?php echo $row["lastname"]; ?>" required oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                    <label for="lastname">Apellido</label>
                </div>

                <div class="input-box">
                    <div>
                        <span class="icon">
                            <ion-icon name="id-card"></ion-icon>
                        </span>
                        <label for="dni">DNI</label>
                        <input type="text" name="dni" id="dni" value="<?php echo $row["dni"]; ?>" required pattern="[0-9]{8}" maxlength="8">
                    </div>
                </div>

                <div class="input-box">
                    <div>
                        <span class="icon">
                            <ion-icon name="calendar-number"></ion-icon>
                        </span>
                        <label for="anios_ingreso">Años en la Empresa (1-4)</label>
                        <input type="text" text="number" name="anios_ingreso" id="anios_ingreso" value="<?php echo $row["anios_ingreso"];?>" required pattern="[1-4]" maxlength="1">
                    </div>
                </div>

                <div class="input-box">
                    <div>
                        <span class="icon">
                            <ion-icon name="phone-portrait"></ion-icon>
                        </span>
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="<?php echo $row["telefono"]; ?>" required pattern="[0-9]{1,15}" maxlength="15">
                    </div>
                </div>

                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="globe"></ion-icon>
                    </span>
                    <input type="text" name="nacionalidad" id="nacionalidad" value="<?php echo $row["nacionalidad"]; ?>" required oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                    <label for="nacionalidad">Nacionalidad</label>
                </div>

                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="location"></ion-icon>
                    </span>
                    <input type="text" name="localidad" id="localidad" value="<?php echo $row["localidad"]; ?>" required oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                    <label for="localidad">Localidad</label>
                </div>

                <button class="btn" type="submit" name="update">Actualizar</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script type ="module" src ="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        // Función para actualizar las opciones de días de vacaciones según los años de ingreso
        const aniosIngresoInput = document.getElementById("anios_ingreso");
        const selectDays = document.getElementById("days");

        aniosIngresoInput.addEventListener("input", function () {
            const aniosIngreso = parseInt(this.value);
            selectDays.innerHTML = ""; // Borrar opciones existentes

            for (let i = 1; i <= aniosIngreso * 7; i++) {
                const option = document.createElement("option");
                option.value = i;
                option.textContent = i + " día" + (i !== 1 ? "s" : ""); // Agregar "s" si hay más de 1 día
                selectDays.appendChild(option);
            }
        });

        // Dispara el evento "input" una vez al cargar la página para inicializar el campo "days"
        const initialAniosIngreso = parseInt(aniosIngresoInput.value);
        if (!isNaN(initialAniosIngreso)) {
            const event = new Event("input", { bubbles: true });
            aniosIngresoInput.dispatchEvent(event);
        }
    </script>
</body>
</html>