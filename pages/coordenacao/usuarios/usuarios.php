<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

$sql = "SELECT idusuario, nome, tipo FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenação - Gerenciar Tipos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div id="mensagem" class="alert" style="display: none;"></div>
            <div class="row">
                <div class="col">
                    <h1>Gerenciar Tipos de Usuário
                        <a href="?page=importar-usuarios" class="btn btn-secondary">
                            <i class="bi bi-upload"></i> Upload lista
                        </a>
                    </h1>
                    <?php
                    switch (@$_REQUEST['page']) {
                        case 'importar-usuarios':
                            include('importar-usuarios.php');
                            break;
                        case 'alterar-tipo':
                            include('alterar-tipo.php');
                            break;
                        case 'excluir-usuarios':
                            include('excluir-usuarios.php');
                            break;
                        case 'alterar-status':
                            include('alterar-status.php');
                            break;
                        default:
                            include('listar-usuarios.php');
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body"></div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
</body>

</html>