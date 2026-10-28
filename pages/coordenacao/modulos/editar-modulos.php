<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$idmodulo = (int) ($_GET['idmodulo'] ?? 0);
$modulo = ['nome_modulo' => '', 'periodo' => PERIODOS[0]];
if ($idmodulo) {
    $modulo = db_um($conn, 'SELECT nome_modulo, periodo FROM modulos WHERE idmodulo = ?', 'i', [$idmodulo])
        ?? redirecionar('modulos.php', 'Módulo não encontrado.', 'error');
}

$titulo = $idmodulo ? 'Editar módulo' : 'Novo módulo';
layout_inicio($titulo);
page_header($titulo, '', botao_voltar('modulos.php'));
?>
<form action="acoes-modulos.php" method="POST" class="card form-narrow">
    <div class="card-body">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="idmodulo" value="<?= $idmodulo ?>">
        <div class="mb-3">
            <label for="nome_modulo" class="form-label">Nome do módulo</label>
            <input type="text" name="nome_modulo" id="nome_modulo" class="form-control" maxlength="90" value="<?= e($modulo['nome_modulo']) ?>" required autofocus>
        </div>
        <div class="mb-0">
            <label for="periodo" class="form-label">Período</label>
            <select name="periodo" id="periodo" class="form-select" required>
                <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= (int) $modulo['periodo'] === $p ? 'selected' : '' ?>><?= $p ?>º período</option><?php endforeach; ?>
            </select>
            <?php if ($idmodulo): ?><div class="form-text">Mudar o período não altera rodízios já gerados.</div><?php endif; ?>
        </div>
        <div class="form-actions">
            <a href="modulos.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Salvar</button>
        </div>
    </div>
</form>
<?php layout_fim(); ?>
