<?php

session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Perguntas de Avaliação</title>

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
            <div class="card">
                <div class="card-body">
                    <?php
                    switch (@$_REQUEST['page']) {
                        case 'editar-perguntas':
                            include('editar-perguntas.php');
                            break;
                        case 'listar-perguntas':
                            include('listar-perguntas.php');
                            break;
                        case 'listar-avaliacoes':
                            include('listar-avaliacoes.php');
                            break;
                        case 'detalhes-avaliacoes':
                            include('detalhes-avaliacoes.php');
                            break;
                        case 'acoes-avaliacoes':
                            include('acoes-avaliacoes.php');
                            break;
                        default:
                            include('pag-base.php');
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
</body>

</html>