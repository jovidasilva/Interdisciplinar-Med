<h1>Editar Módulo</h1>
<?php
$sql = "SELECT * FROM modulos WHERE idmodulo=" . intval($_REQUEST['idmodulo']);
$res = $conn->query($sql);

if (!$res) {
    die("Erro na consulta: " . $conn->error);
}

$row = $res->fetch_object();
if (!$row) {
    die("Módulo não encontrado.");
}
?>
<form action="acoes-modulos.php" method="POST">
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="idmodulo" value="<?php echo intval($row->idmodulo); ?>">

    <div class="mb-3">
        <label>Nome do Módulo</label>
        <input type="text" name="nome_modulo" value="<?php echo htmlspecialchars($row->nome_modulo); ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Período</label>
        <select name="periodo" class="form-select">
            <option value="9" <?php echo ($row->periodo == 9) ? 'selected' : ''; ?>>9º Período</option>
            <option value="10" <?php echo ($row->periodo == 10) ? 'selected' : ''; ?>>10º Período</option>
            <option value="11" <?php echo ($row->periodo == 11) ? 'selected' : ''; ?>>11º Período</option>
            <option value="12" <?php echo ($row->periodo == 12) ? 'selected' : ''; ?>>12º Período</option>
        </select>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-modulos" class="btn btn-secondary">Voltar</a>
    </div>
</form>