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
    <title>Início - Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-aluno.php'); ?>
    </header>
    <main>
        <div class="container mt-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h2>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
                    <p class="text-muted mb-0">Acesse rapidamente as principais áreas do seu internato.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="horarios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-alarm" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Horários</h5>
                                <p class="text-muted mb-0">Consulte seus horários de atividades.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="notas.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-journal-text" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Notas</h5>
                                <p class="text-muted mb-0">Veja suas avaliações e notas.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="rodizios.php" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Rodízios</h5>
                                <p class="text-muted mb-0">Acompanhe seus rodízios.</p>
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