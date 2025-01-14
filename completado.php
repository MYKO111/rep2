<?php
require_once "conexion_pdo.php";
require_once "config/config.php";
$dbConnection = new ConectaBD();
$pdo = $dbConnection->getConBD();

// Obtener el ID de transacción desde la URL, con un valor predeterminado de 0 si no está presente
$id_transaccion = isset($_GET['key']) ? $_GET['key'] : 0;

$error = '';

// Verificar si el ID de transacción es válido
if ($id_transaccion == '') {
    $error = 'Error al procesar la peticion';
} else {
    $query = "SELECT count(id_compra) as count FROM compra WHERE id_transaccion = ? AND estatus=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_transaccion, 'COMPLETED']);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Si se encuentra al menos un registro, proceder a obtener los detalles
    if ($count > 0) {
        $query = "SELECT id_compra, fecha, correo, total FROM compra WHERE id_transaccion = ? AND estatus=? LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_transaccion, 'COMPLETED']);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Extraer los datos de la compra
        $id_compra = $resultado['id_compra'];
        $total = $resultado['total'];
        $fecha = $resultado['fecha'];

        // Obtener los detalles de los productos asociados a la compra
        $sqlDet = $pdo->prepare("SELECT nombre, precio, cantidad FROM detalle_compra WHERE id_compra = ?");
        $sqlDet->execute([$id_compra]);
    } else {
        $error = 'Error al comprobar la compra';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Ariel_Caicedo">
    <title>Tienda de juegos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Header Area Start -->
    <?php include 'menu.php'; ?>

    <!-- Contenido -->
    <main>
        <div class="container">
            <?php if (strlen($error) > 0) { ?>
                <div class="row">
                    <div class="col">
                        <h3><?php echo $error; ?></h3>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row">
                    <div class="col">
                        <b>Folio de la compra: </b><?php echo $id_transaccion; ?><br>
                        <b>Fecha de compra: </b><?php echo $fecha; ?><br>
                        <b>Total: </b><?php echo MONEDA . ' ' . number_format($total, 2, ',', '.'); ?><br>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_det = $sqlDet->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <?php $importe = $row_det['precio'] * $row_det['cantidad']; ?>
                                    <tr>
                                        <td><?php echo $row_det['cantidad']; ?></td>
                                        <td><?php echo $row_det['nombre']; ?></td>
                                        <td><?php echo $importe; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>

</html>