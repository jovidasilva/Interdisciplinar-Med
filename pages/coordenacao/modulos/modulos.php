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
    <title>Módulos</title>
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
                        case 'editar-modulos':
                            include('editar-modulos.php');
                            break;
                        case 'registrar-modulos':
                            include('registrar-modulos.php');
                            break;
                        case 'importar-modulos':
                            include('importar-modulos.php');
                            break;
                        case 'visualizar-modulos':
                            include('visualizar-modulos.php');
                            break;
                            case 'upload-alunos':
                                include('upload-alunos.php');
                                break;
                        case 'visualizar-preceptor':
                            include('visualizar-preceptor.php');    
                            break;
                        default:
                            include('listar-modulos.php');
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>