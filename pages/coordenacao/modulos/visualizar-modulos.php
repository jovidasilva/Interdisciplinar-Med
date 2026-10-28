<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$idmodulo = (int) ($_GET['idmodulo'] ?? 0);
$modulo = db_um($conn, 'SELECT idmodulo, nome_modulo, periodo FROM modulos WHERE idmodulo = ?', 'i', [$idmodulo])
    ?? redirecionar('modulos.php', 'Módulo não encontrado.', 'error');

$matriculados = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro, u.ativo FROM modulos_alunos ma JOIN usuarios u ON u.idusuario = ma.idusuario
    WHERE ma.idmodulo = ? ORDER BY u.nome', 'i', [$idmodulo]);
$disponiveis = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro FROM usuarios u
    WHERE u.tipo = 0 AND u.ativo = 1 AND u.periodo = ?
      AND NOT EXISTS (SELECT 1 FROM modulos_alunos ma WHERE ma.idmodulo = ? AND ma.idusuario = u.idusuario)
    ORDER BY u.nome', 'ii', [$modulo['periodo'], $idmodulo]);

layout_inicio($modulo['nome_modulo']);
page_header($modulo['nome_modulo'], $modulo['periodo'] . 'º período · Matrículas são criadas automaticamente ao gerar um ciclo de rodízio; use esta tela para ajustes.',
    botao_voltar('modulos.php') . '<a href="editar-modulos.php?idmodulo=' . $idmodulo . '" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Editar</a>');
?>
<div class="assoc mb-4">
    <form method="POST" action="acoes-modulos.php" class="card" data-require-check="alunos[]" data-confirm="Remover os alunos selecionados deste módulo?">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="desmatricular">
        <input type="hidden" name="idmodulo" value="<?= $idmodulo ?>">
        <div class="card-header">
            <h2>Matriculados <span class="text-muted fw-normal">(<?= count($matriculados) ?>)</span></h2>
            <?php if ($matriculados): ?><label class="small"><input type="checkbox" class="form-check-input me-1" data-select-all="sel-mat"> Todos</label><?php endif; ?>
        </div>
        <?php if (!$matriculados): ?>
            <div class="card-body"><?= empty_state('Nenhum aluno matriculado.', 'bi-mortarboard') ?></div>
        <?php else: ?>
            <div class="px-3 pt-3"><input type="search" class="form-control form-control-sm" placeholder="Filtrar…" data-filter="#listaMat" aria-label="Filtrar matriculados"></div>
            <ul class="check-list" id="listaMat">
                <?php foreach ($matriculados as $a): ?>
                    <li data-filter-item><label><input type="checkbox" class="form-check-input sel-mat" name="alunos[]" value="<?= (int) $a['idusuario'] ?>"> <?= e($a['nome']) ?><?= (int) $a['ativo'] ? '' : ' ' . badge_status('inativo') ?><small><?= e($a['registro']) ?></small></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer"><span></span><button class="btn btn-sm btn-outline-danger"><i class="bi bi-dash-circle"></i> Remover selecionados</button></div>
        <?php endif; ?>
    </form>

    <form method="POST" action="acoes-modulos.php" class="card" data-require-check="alunos[]">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="matricular">
        <input type="hidden" name="idmodulo" value="<?= $idmodulo ?>">
        <div class="card-header">
            <h2>Alunos do <?= (int) $modulo['periodo'] ?>º período fora do módulo <span class="text-muted fw-normal">(<?= count($disponiveis) ?>)</span></h2>
            <?php if ($disponiveis): ?><label class="small"><input type="checkbox" class="form-check-input me-1" data-select-all="sel-disp"> Todos</label><?php endif; ?>
        </div>
        <?php if (!$disponiveis): ?>
            <div class="card-body"><?= empty_state('Todos os alunos ativos do período já estão matriculados.', 'bi-check2-circle') ?></div>
        <?php else: ?>
            <div class="px-3 pt-3"><input type="search" class="form-control form-control-sm" placeholder="Filtrar…" data-filter="#listaDisp" aria-label="Filtrar disponíveis"></div>
            <ul class="check-list" id="listaDisp">
                <?php foreach ($disponiveis as $a): ?>
                    <li data-filter-item><label><input type="checkbox" class="form-check-input sel-disp" name="alunos[]" value="<?= (int) $a['idusuario'] ?>"> <?= e($a['nome']) ?><small><?= e($a['registro']) ?></small></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer"><span></span><button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Matricular selecionados</button></div>
        <?php endif; ?>
    </form>
</div>

<form method="POST" action="acoes-modulos.php" enctype="multipart/form-data" class="card">
    <div class="card-header"><h2>Matricular por planilha</h2><span class="text-muted">CSV com o RA (ou nome) do aluno na primeira coluna; o cabeçalho é ignorado</span></div>
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="upload">
        <input type="hidden" name="idmodulo" value="<?= $idmodulo ?>">
        <input type="file" name="file" class="form-control" style="max-width: 420px" accept=".csv,text/csv" required aria-label="Arquivo CSV">
        <button class="btn btn-outline-primary"><i class="bi bi-upload"></i> Enviar</button>
    </div>
</form>
<?php layout_fim(); ?>
