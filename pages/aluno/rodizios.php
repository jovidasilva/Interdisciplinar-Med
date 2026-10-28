<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_ALUNO);

$rodizios = db_todos($conn, "SELECT DISTINCT r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo, sg.nome_subgrupo
    FROM alunos_subgrupos asg
    JOIN subgrupos sg ON sg.idsubgrupo = asg.idsubgrupo
    JOIN rodizios_subgrupos rs ON rs.idsubgrupo = asg.idsubgrupo
    JOIN rodizios r ON r.idrodizio = rs.idrodizio
    JOIN modulos m ON m.idmodulo = r.idmodulo
    WHERE asg.idusuario = ?
    ORDER BY r.inicio", 'i', [usuario_id()]);

layout_inicio('Meus rodízios');
page_header('Meus rodízios', 'Módulos pelos quais seu subgrupo passa, em ordem cronológica.');
?>
<div class="card">
    <?php if (!$rodizios): ?>
        <div class="card-body"><?= empty_state('Você ainda não está em nenhum rodízio.', 'bi-arrow-repeat') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Módulo</th><th>Subgrupo</th><th>Período</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($rodizios as $r): ?>
                        <tr>
                            <td class="fw-medium"><?= e($r['nome_modulo']) ?></td>
                            <td><?= e($r['nome_subgrupo']) ?></td>
                            <td><?= (int) $r['periodo'] ?>º</td>
                            <td><?= e(data_br($r['inicio'])) ?></td>
                            <td><?= e(data_br($r['fim'])) ?></td>
                            <td><?= badge_status(status_periodo($r['inicio'], $r['fim'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
