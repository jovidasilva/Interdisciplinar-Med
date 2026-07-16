<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

include('../../cfg/config.php');

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h2>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
                    <p class="text-muted mb-0">Acesse rapidamente as principais áreas de coordenação.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="usuarios/usuarios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-person-check" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Usuários</h5>
                                <p class="text-muted mb-0">Gerencie alunos, preceptores e coordenadores.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="modulos/modulos.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-grid" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Módulos</h5>
                                <p class="text-muted mb-0">Gerencie módulos e associações.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="unidades/unidades.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-building" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Unidades</h5>
                                <p class="text-muted mb-0">Gerencie unidades e departamentos.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="rodizios/rodizios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Rodízios</h5>
                                <p class="text-muted mb-0">Gerencie rodízios dos alunos.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="horarios/horarios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-alarm" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Horários</h5>
                                <p class="text-muted mb-0">Gerencie horários de atividades.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="avaliacoes/avaliacoes.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-journal-text" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Avaliações</h5>
                                <p class="text-muted mb-0">Gerencie avaliações e perguntas.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="relatorios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Relatórios</h5>
                                <p class="text-muted mb-0">Consulte relatórios gerais.</p>
                            </div>
                        </div>
                    </a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>