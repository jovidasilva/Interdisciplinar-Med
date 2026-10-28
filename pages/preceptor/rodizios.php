<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$rodizios = db_todos($conn, "SELECT r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo,
        GROUP_CONCAT(DISTINCT sg.nome_subgrupo ORDER BY sg.nome_subgrupo SEPARATOR ', ') AS subgrupos,
        COUNT(DISTINCT asg.idusuario) AS alunos
    FROM preceptores_modulos pm
    JOIN rodizios r ON r.idmodulo = pm.idmodulo
    JOIN modulos m ON m.idmodulo = r.idmodulo
    LEFT JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
    LEFT JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
    LEFT JOIN alunos_subgrupos asg ON asg.idsubgrupo = sg.idsubgrupo
    WHERE pm.idusuario = ?
    GROUP BY r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo
    ORDER BY r.inicio DESC", 'i', [usuario_id()]);

layout_inicio('Rodízios');
page_header('Rodízios dos meus módulos', 'Quais subgrupos passam pelos seus módulos e quando.');
?>
<div class="card">
    <?php if (!$rodizios): ?>
        <div class="card-body"><?= empty_state('Não há rodízios nos seus módulos no momento.', 'bi-arrow-repeat') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Módulo</th><th>Período</th><th>Início</th><th>Fim</th><th>Subgrupos</th><th class="text-end">Alunos</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($rodizios as $r): ?>
                        <tr>
                            <td class="fw-medium"><?= e($r['nome_modulo']) ?></td>
                            <td><?= (int) $r['periodo'] ?>º</td>
                            <td><?= e(data_br($r['inicio'])) ?></td>
                            <td><?= e(data_br($r['fim'])) ?></td>
                            <td><?php foreach (array_filter(explode(', ', (string) $r['subgrupos'])) as $sg): ?><span class="chip"><?= e($sg) ?></span><?php endforeach; ?></td>
                            <td class="text-end"><?= (int) $r['alunos'] ?></td>
                            <td><?= badge_status(status_periodo($r['inicio'], $r['fim'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
