<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
require_once __DIR__ . '/' . str_repeat('../', 3) . 'includes/csrf.php';
?>
<h1>Cadastrar nova unidade</h1>
<form action="acoes-unidades.php" method="POST">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="acao" value="cadastrar">
    <div class="mb-3">
        <label>Nome da unidade</label>
        <input type="text" name="nome_unidade" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Endereço</label>
        <input type="text" name="endereco_unidade" class="form-control" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-unidades" class="btn btn-secondary">Voltar</a>
    </div>
</form>
