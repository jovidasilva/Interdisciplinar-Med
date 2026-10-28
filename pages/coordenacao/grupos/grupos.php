<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$ciclos = db_todos($conn, 'SELECT idciclo, descricao, periodo FROM rodizio_ciclos ORDER BY idciclo DESC');
$idciclo = (int) ($_GET['ciclo'] ?? ($ciclos[0]['idciclo'] ?? 0));
$ciclo = null;
foreach ($ciclos as $c) {
    if ((int) $c['idciclo'] === $idciclo) {
        $ciclo = $c;
    }
}

$grupos = [];
if ($ciclo) {
    $linhas = db_todos($conn, "SELECT g.idgrupo, g.nome_grupo, sg.idsubgrupo, sg.nome_subgrupo,
            (SELECT COUNT(*) FROM alunos_subgrupos asg WHERE asg.idsubgrupo = sg.idsubgrupo) AS alunos,
            (SELECT GROUP_CONCAT(CONCAT(m.nome_modulo, '|', r.inicio, '|', r.fim) ORDER BY r.inicio SEPARATOR '§')
                FROM rodizios_subgrupos rs JOIN rodizios r ON r.idrodizio = rs.idrodizio JOIN modulos m ON m.idmodulo = r.idmodulo
                WHERE rs.idsubgrupo = sg.idsubgrupo) AS trajeto
        FROM grupos g JOIN subgrupos sg ON sg.idgrupo = g.idgrupo
        WHERE g.idciclo = ? ORDER BY g.nome_grupo, sg.nome_subgrupo", 'i', [$idciclo]);
    foreach ($linhas as $l) {
        $grupos[$l['idgrupo']]['nome'] = $l['nome_grupo'];
        $grupos[$l['idgrupo']]['trajeto'] ??= $l['trajeto'];
        $grupos[$l['idgrupo']]['subgrupos'][] = $l;
    }
}

layout_inicio('Grupos');
page_header('Grupos', 'Grupos e subgrupos são criados ao gerar um ciclo de rodízio. Aqui você ajusta quem está em cada subgrupo.');
?>
<?php if (!$ciclos): ?>
    <div class="card"><div class="card-body"><?= empty_state('Nenhum ciclo de rodízio gerado ainda.', 'bi-people', '<a href="../rodizios/gerar-rodizios.php" class="btn btn-primary">Gerar ciclo</a>') ?></div></div>
<?php else: ?>
    <form method="GET" class="d-flex gap-2 align-items-center mb-3">
        <label for="ciclo" class="form-label mb-0 text-muted">Ciclo</label>
        <select name="ciclo" id="ciclo" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($ciclos as $c): ?><option value="<?= (int) $c['idciclo'] ?>" <?= (int) $c['idciclo'] === $idciclo ? 'selected' : '' ?>><?= e($c['descricao']) ?></option><?php endforeach; ?>
        </select>
    </form>
    <div class="row g-3">
        <?php foreach ($grupos as $g): ?>
            <div class="col-lg-6 col-xxl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2>Grupo <?= e($g['nome']) ?></h2>
                        <span class="status status-neutral"><?= array_sum(array_column($g['subgrupos'], 'alunos')) ?> alunos</span>
                    </div>
                    <div class="card-body pb-2">
                        <div class="small text-muted mb-2">Trajeto</div>
                        <ol class="ps-3 small mb-0">
                            <?php foreach (array_filter(explode('§', (string) $g['trajeto'])) as $t): [$mod, $ini, $fim] = explode('|', $t); ?>
                                <li><span class="fw-medium"><?= e($mod) ?></span> <span class="text-muted"><?= e(data_br($ini)) ?> – <?= e(data_br($fim)) ?></span></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <ul class="check-list">
                        <?php foreach ($g['subgrupos'] as $sg): ?>
                            <li><a class="d-flex align-items-center gap-3 px-4 py-2 text-reset text-decoration-none" href="ver-alunos.php?idsubgrupo=<?= (int) $sg['idsubgrupo'] ?>">
                                <span class="fw-medium">Subgrupo <?= e($sg['nome_subgrupo']) ?></span>
                                <span class="ms-auto text-muted small"><?= (int) $sg['alunos'] ?> aluno(s)</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php layout_fim(); ?>
