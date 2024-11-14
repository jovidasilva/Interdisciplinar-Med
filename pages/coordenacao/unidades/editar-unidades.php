<h1>Editar Módulo</h1>
<?php
$sql = "SELECT * FROM unidades WHERE idunidade=" . intval($_REQUEST['idunidade']);
$res = $conn->query($sql);

if (!$res) {
    die("Erro na consulta: " . $conn->error);
}

$row = $res->fetch_object();
if (!$row) {
    die("Módulo não encontrado.");
}
?>
<form action="acoes-unidades.php" method="POST">
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="idunidade" value="<?php echo intval($row->idunidade); ?>">
    <div class="mb-3">
        <label>Nome da unidade</label>
        <input type="text" name="nome_unidade" value="<?php echo htmlspecialchars($row->nome_unidade); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Endereco</label>
        <input type="text" name="endereco_unidade" value="<?php echo htmlspecialchars($row->endereco_unidade); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-unidades" class="btn btn-secondary">Voltar</a>
    </div>
</form>
