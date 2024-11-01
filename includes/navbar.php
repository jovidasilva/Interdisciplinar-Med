<nav class="navbar navbar-home">
    <div class="container-fluid">
        <a href="<?php echo BASE_URL; ?>/includes/redirecionar.php" class="nodec">
            <h2 class="navbar-brand">SISTEMA MEDICINA</h2>
        </a>
        <div class="dropdown">
            <span class="navbar-text me-3">Olá, <?php echo htmlspecialchars($_SESSION["nome"]); ?></span>
            <span class="icone-perfil no-caret" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
            </span>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/includes/perfil.php">Perfil</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/cadastro_e_login/logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>
