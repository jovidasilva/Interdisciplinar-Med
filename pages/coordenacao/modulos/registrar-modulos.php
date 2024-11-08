<h1>Cadastrar novo módulo</h1>
<form action="acoes-modulos.php" method="POST">
    <input type="hidden" name="acao" value="cadastrar">
    <div class="mb-3">
        <label>Nome do módulo</label>
        <input type="text" name="nome_modulo" class="form-control" required>
    </div>
    <div class="mb-3">
        <select name="periodo" class="form-select">
            <option value="9">9 periodo</option>
            <option value="10">10 periodo</option>
            <option value="11">11 periodo</option>
            <option value="12">12 periodo</option>
        </select>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="?page=listar-modulos" class="btn btn-secondary">Voltar</a>
    </div>
</form>