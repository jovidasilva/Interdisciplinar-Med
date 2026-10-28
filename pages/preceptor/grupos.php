<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$linhas = db_todos($conn, "SELECT sg.idsubgrupo, sg.nome_subgrupo, c.descricao AS ciclo, u.nome AS aluno, u.registro
    FROM (SELECT DISTINCT rs.idsubgrupo
          FROM preceptores_modulos pm
          JOIN rodizios r ON r.idmodulo = pm.idmodulo
          JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
          WHERE pm.idusuario = ?) meus
    JOIN subgrupos sg ON sg.idsubgrupo = meus.idsubgrupo
    JOIN grupos g ON g.idgrupo = sg.idgrupo
    LEFT JOIN rodizio_ciclos c ON c.idciclo = g.idciclo
    LEFT JOIN alunos_subgrupos asg ON asg.idsubgrupo = sg.idsubgrupo
    LEFT JOIN usuarios u ON u.idusuario = asg.idusuario
    ORDER BY g.idciclo DESC, sg.nome_subgrupo, u.nome", 'i', [usuario_id()]);

$subgrupos = [];
foreach ($linhas as $l) {
    $subgrupos[$l['idsubgrupo']] ??= ['nome' => $l['nome_subgrupo'], 'ciclo' => $l['ciclo'], 'alunos' => []];
    if ($l['aluno'] !== null) {
        $subgrupos[$l['idsubgrupo']]['alunos'][] = $l;
    }
}

layout_inicio('Grupos');
page_header('Meus grupos', 'Subgrupos que passam pelos seus módulos e seus alunos.');
?>
<?php if (!$subgrupos): ?>
    <div class="card"><div class="card-body"><?= empty_state('Você ainda não possui subgrupos vinculados aos seus módulos.', 'bi-people') ?></div></div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($subgrupos as $sg): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <div>
                            <h2>Subgrupo <?= e($sg['nome']) ?></h2>
                            <?php if ($sg['ciclo']): ?><span class="text-muted"><?= e($sg['ciclo']) ?></span><?php endif; ?>
                        </div>
                        <span class="status status-neutral"><?= count($sg['alunos']) ?> aluno(s)</span>
                    </div>
                    <?php if (!$sg['alunos']): ?>
                        <div class="card-body text-muted">Nenhum aluno neste subgrupo.</div>
                    <?php else: ?>
                        <ul class="check-list">
                            <?php foreach ($sg['alunos'] as $a): ?><li><label><?= e($a['aluno']) ?><small><?= e($a['registro']) ?></small></label></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php layout_fim(); ?>
