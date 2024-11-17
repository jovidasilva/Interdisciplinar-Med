<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="row">
                <div class="col">
                    <?php
                    switch (@$_REQUEST['page']) {
                        case 'editar-unidades':
                            include('editar-unidades.php');
                            break;
                        case 'registrar-unidades':
                            include('registrar-unidades.php');
                            break;
                        case 'visualizar-unidade':
                            include('visualizar-unidade.php');
                            break;
                        case 'associar-modulos':
                            include('associar-modulos.php');
                            break;
                        case 'dessassociar-modulos':
                            include('dessassociar-modulos.php');
                            break;
                        case 'visualizar-preceptor':
                            include('visualizar-preceptor.php');
                            break;
                        case 'acoes-preceptor':
                            include('acoes-preceptor.php');
                            break;
                        default:
                            include('listar-unidades.php');
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>