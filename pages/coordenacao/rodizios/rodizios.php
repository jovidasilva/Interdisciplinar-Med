<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$filtroPeriodo = in_array((int) ($_GET['periodo'] ?? 0), PERIODOS, true) ? (int) $_GET['periodo'] : 0;

$linhas = db_todos($conn, "SELECT c.idciclo, c.descricao, c.periodo, r.inicio, r.fim, m.nome_modulo, g.idgrupo, g.nome_grupo,
        (SELECT COUNT(DISTINCT asg.idusuario) FROM grupos g2 JOIN subgrupos s2 ON s2.idgrupo = g2.idgrupo
            JOIN alunos_subgrupos asg ON asg.idsubgrupo = s2.idsubgrupo WHERE g2.idciclo = c.idciclo) AS total_alunos
    FROM rodizio_ciclos c
    JOIN rodizios r ON r.idciclo = c.idciclo
    JOIN modulos m ON m.idmodulo = r.idmodulo
    JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
    JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
    JOIN grupos g ON g.idgrupo = sg.idgrupo
    WHERE (? = 0 OR c.periodo = ?)
    GROUP BY c.idciclo, c.descricao, c.periodo, r.inicio, r.fim, m.nome_modulo, g.idgrupo, g.nome_grupo
    ORDER BY c.idciclo DESC, r.inicio, g.nome_grupo", 'ii', [$filtroPeriodo, $filtroPeriodo]);

// Monta, por ciclo, a matriz grupo × janela => módulo.
$ciclos = [];
foreach ($linhas as $l) {
    $c = &$ciclos[$l['idciclo']];
    $c['descricao'] ??= $l['descricao'];
    $c['periodo'] ??= (int) $l['periodo'];
    $c['alunos'] ??= (int) $l['total_alunos'];
    $c['janelas'][$l['inicio']] = $l['fim'];
    $c['grupos'][$l['idgrupo']] = $l['nome_grupo'];
    $c['celulas'][$l['idgrupo']][$l['inicio']] = $l['nome_modulo'];
    unset($c);
}

layout_inicio('Rodízios');
page_header(
    'Rodízios',
    'Cada ciclo divide os alunos de um período em grupos que passam por todos os módulos escolhidos, um por janela de datas.',
    '<a href="gerar-rodizios.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Gerar ciclo</a>'
);
?>
<form method="GET" class="d-flex gap-2 align-items-center mb-3">
    <label for="periodo" class="form-label mb-0 text-muted">Período</label>
    <select name="periodo" id="periodo" class="form-select w-auto" onchange="this.form.submit()">
        <option value="0">Todos</option>
        <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= $p === $filtroPeriodo ? 'selected' : '' ?>><?= $p ?>º período</option><?php endforeach; ?>
    </select>
</form>

<?php if (!$ciclos): ?>
    <div class="card"><div class="card-body">
        <?= empty_state('Nenhum ciclo de rodízio gerado' . ($filtroPeriodo ? ' para este período' : '') . '.', 'bi-arrow-repeat', '<a href="gerar-rodizios.php" class="btn btn-primary">Gerar o primeiro ciclo</a>') ?>
    </div></div>
<?php endif; ?>

<div class="gap-list">
<?php foreach ($ciclos as $idciclo => $c):
    ksort($c['janelas']);
    asort($c['grupos']);
    $inicio = array_key_first($c['janelas']);
    $fim = end($c['janelas']); ?>
    <div class="card">
        <div class="card-header">
            <div>
                <h2><?= e($c['descricao']) ?></h2>
                <span class="text-muted"><?= $c['periodo'] ?>º período · <?= e(data_br($inicio)) ?> a <?= e(data_br($fim)) ?> · <?= count($c['grupos']) ?> grupos · <?= $c['alunos'] ?> alunos</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <?= badge_status(status_periodo($inicio, $fim)) ?>
                <a href="../grupos/grupos.php?ciclo=<?= (int) $idciclo ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people"></i> Grupos</a>
                <?= form_acao('acoes-rodizios.php', ['acao' => 'excluir', 'idciclo' => $idciclo], '<i class="bi bi-trash"></i> Excluir', 'btn btn-sm btn-outline-danger',
                    'Excluir este ciclo remove seus grupos, subgrupos, rodízios e os horários ligados a esses subgrupos. As matrículas e avaliações são mantidas.') ?>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <?php $n = 1; foreach ($c['janelas'] as $ini => $f): ?>
                            <th>Janela <?= $n++ ?><br><span class="fw-normal text-lowercase"><?= e(data_br($ini)) ?> – <?= e(data_br($f)) ?></span></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($c['grupos'] as $idgrupo => $nome): ?>
                        <tr>
                            <td class="fw-semibold">Grupo <?= e($nome) ?></td>
                            <?php foreach (array_keys($c['janelas']) as $ini): ?>
                                <td><?= e($c['celulas'][$idgrupo][$ini] ?? '—') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php layout_fim(); ?>
