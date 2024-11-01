<h1>Editar Módulo</h1>
<?php
$sql = "SELECT * FROM unidades WHERE id_unidade=" . intval($_REQUEST['id_unidade']);
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
    <input type="hidden" name="id_unidade" value="<?php echo intval($row->id_unidade); ?>">
    <div class="mb-3">
        <label>Nome da unidade</label>
        <input type="text" name="nome_unidade" value="<?php echo htmlspecialchars($row->nome_unidade); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-unidades" class="btn btn-secondary">Voltar</a>
    </div>
</form>
