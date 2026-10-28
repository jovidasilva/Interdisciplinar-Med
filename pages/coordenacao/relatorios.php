<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$geral = db_um($conn, 'SELECT COUNT(*) AS total, AVG(nota) AS media FROM avaliacoes');

$porModulo = db_todos($conn, "SELECT m.nome_modulo, m.periodo,
        COUNT(DISTINCT ma.idusuario) AS matriculados,
        COUNT(DISTINCT a.idavaliacao) AS avaliacoes,
        AVG(a.nota) AS media
    FROM modulos m
    LEFT JOIN modulos_alunos ma ON ma.idmodulo = m.idmodulo
    LEFT JOIN avaliacoes a ON a.idmodulo = m.idmodulo
    GROUP BY m.idmodulo, m.nome_modulo, m.periodo
    ORDER BY m.periodo, m.nome_modulo");

$porPreceptor = db_todos($conn, "SELECT u.nome,
        COUNT(a.idavaliacao) AS avaliacoes, AVG(a.nota) AS media,
        (SELECT COUNT(*) FROM preceptores_modulos pm JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo
            WHERE pm.idusuario = u.idusuario
              AND NOT EXISTS (SELECT 1 FROM avaliacoes x WHERE x.idaluno = ma.idusuario AND x.idmodulo = ma.idmodulo AND x.idpreceptor = u.idusuario)) AS pendentes
    FROM usuarios u
    LEFT JOIN avaliacoes a ON a.idpreceptor = u.idusuario
    WHERE u.tipo IN (1, 3) AND u.ativo = 1
    GROUP BY u.idusuario, u.nome
    ORDER BY pendentes DESC, u.nome");

// Matriculados em módulo cujo rodízio já terminou, sem nenhuma avaliação nesse módulo.
$semAvaliacao = db_todos($conn, "SELECT DISTINCT u.nome, u.registro, m.nome_modulo, r.fim
    FROM modulos_alunos ma
    JOIN usuarios u ON u.idusuario = ma.idusuario AND u.ativo = 1
    JOIN modulos m ON m.idmodulo = ma.idmodulo
    JOIN alunos_subgrupos asg ON asg.idusuario = u.idusuario
    JOIN rodizios_subgrupos rs ON rs.idsubgrupo = asg.idsubgrupo
    JOIN rodizios r ON r.idrodizio = rs.idrodizio AND r.idmodulo = m.idmodulo AND r.fim < CURDATE()
    WHERE NOT EXISTS (SELECT 1 FROM avaliacoes a WHERE a.idaluno = u.idusuario AND a.idmodulo = m.idmodulo)
    ORDER BY r.fim DESC, u.nome
    LIMIT 200");

function nota_fmt($v): string
{
    return $v !== null ? number_format((float) $v, 2, ',', '.') : '—';
}

layout_inicio('Relatórios');
page_header('Relatórios', 'Indicadores de avaliação por módulo e por preceptor.');
?>
<div class="stats">
    <?= stat_tile('Avaliações realizadas', (string) $geral['total'], 'bi-clipboard-check', 'avaliacoes/avaliacoes.php') ?>
    <?= stat_tile('Média geral', nota_fmt($geral['media']), 'bi-graph-up') ?>
    <?= stat_tile('Rodízios concluídos sem avaliação', (string) count($semAvaliacao), 'bi-exclamation-triangle') ?>
</div>

<div class="card">
    <div class="card-header"><h2>Por módulo</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Módulo</th><th>Período</th><th class="text-end">Matriculados</th><th class="text-end">Avaliações</th><th style="width: 30%">Cobertura</th><th class="text-end">Média</th></tr></thead>
            <tbody>
                <?php foreach ($porModulo as $m): $cob = $m['matriculados'] ? min(100, round($m['avaliacoes'] / $m['matriculados'] * 100)) : 0; ?>
                    <tr>
                        <td class="fw-medium"><?= e($m['nome_modulo']) ?></td>
                        <td><?= (int) $m['periodo'] ?>º</td>
                        <td class="text-end"><?= (int) $m['matriculados'] ?></td>
                        <td class="text-end"><?= (int) $m['avaliacoes'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px" role="progressbar" aria-valuenow="<?= $cob ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Cobertura de avaliações">
                                    <div class="progress-bar" style="width: <?= $cob ?>%; background: var(--brand)"></div>
                                </div>
                                <span class="small text-muted tabular" style="width: 3ch"><?= $cob ?>%</span>
                            </div>
                        </td>
                        <td class="text-end fw-semibold"><?= nota_fmt($m['media']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$porModulo): ?><tr><td colspan="6"><?= empty_state('Nenhum módulo cadastrado.') ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Por preceptor</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Preceptor</th><th class="text-end">Avaliações</th><th class="text-end">Pendentes</th><th class="text-end">Média atribuída</th></tr></thead>
            <tbody>
                <?php foreach ($porPreceptor as $p): ?>
                    <tr>
                        <td class="fw-medium"><?= e($p['nome']) ?></td>
                        <td class="text-end"><?= (int) $p['avaliacoes'] ?></td>
                        <td class="text-end"><?= (int) $p['pendentes'] ? '<span class="status status-warning">' . (int) $p['pendentes'] . '</span>' : '0' ?></td>
                        <td class="text-end"><?= nota_fmt($p['media']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$porPreceptor): ?><tr><td colspan="4"><?= empty_state('Nenhum preceptor ativo.') ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Rodízios concluídos sem avaliação</h2><span class="text-muted">Aluno passou pelo módulo e ninguém o avaliou</span></div>
    <?php if (!$semAvaliacao): ?>
        <div class="card-body"><?= empty_state('Nenhuma pendência: todos os rodízios concluídos têm avaliação.', 'bi-check2-circle') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Aluno</th><th>RA</th><th>Módulo</th><th>Rodízio terminou em</th></tr></thead>
                <tbody>
                    <?php foreach ($semAvaliacao as $s): ?>
                        <tr><td><?= e($s['nome']) ?></td><td><?= e($s['registro']) ?></td><td><?= e($s['nome_modulo']) ?></td><td><?= e(data_br($s['fim'])) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
