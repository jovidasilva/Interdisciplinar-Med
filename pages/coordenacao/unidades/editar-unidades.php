<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$idunidade = (int) ($_GET['idunidade'] ?? 0);
$unidade = ['nome_unidade' => '', 'endereco_unidade' => ''];
if ($idunidade) {
    $unidade = db_um($conn, 'SELECT nome_unidade, endereco_unidade FROM unidades WHERE idunidade = ?', 'i', [$idunidade])
        ?? redirecionar('unidades.php', 'Unidade não encontrada.', 'error');
}
$voltar = $idunidade ? 'visualizar-unidade.php?idunidade=' . $idunidade : 'unidades.php';

$titulo = $idunidade ? 'Editar unidade' : 'Nova unidade';
layout_inicio($titulo);
page_header($titulo, '', botao_voltar($voltar));
?>
<form action="acoes-unidades.php" method="POST" class="card form-narrow">
    <div class="card-body">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="idunidade" value="<?= $idunidade ?>">
        <div class="mb-3">
            <label for="nome_unidade" class="form-label">Nome da unidade</label>
            <input type="text" name="nome_unidade" id="nome_unidade" class="form-control" maxlength="90" value="<?= e($unidade['nome_unidade']) ?>" required autofocus>
        </div>
        <div class="mb-0">
            <label for="endereco_unidade" class="form-label">Endereço</label>
            <input type="text" name="endereco_unidade" id="endereco_unidade" class="form-control" maxlength="90" value="<?= e($unidade['endereco_unidade']) ?>">
        </div>
        <div class="form-actions">
            <a href="<?= e($voltar) ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Salvar</button>
        </div>
    </div>
</form>
<?php layout_fim(); ?>
