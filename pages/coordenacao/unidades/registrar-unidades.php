<h1>Cadastrar nova unidade</h1>
<form action="acoes-unidades.php" method="POST">
    <input type="hidden" name="acao" value="cadastrar">
    <div class="mb-3">
        <label>Nome da unidade</label>
        <input type="text" name="nome_unidade" class="form-control" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-unidades" class="btn btn-secondary">Voltar</a>
    </div>
</form>
