<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$n = db_um($conn, "SELECT
    (SELECT COUNT(*) FROM usuarios WHERE tipo = 0 AND ativo = 1) AS alunos,
    (SELECT COUNT(*) FROM usuarios WHERE tipo IN (1, 3) AND ativo = 1) AS preceptores,
    (SELECT COUNT(*) FROM usuarios WHERE tipo = -1) AS pendentes,
    (SELECT COUNT(*) FROM modulos) AS modulos,
    (SELECT COUNT(*) FROM unidades) AS unidades,
    (SELECT COUNT(*) FROM rodizios WHERE CURDATE() BETWEEN inicio AND fim) AS rodizios_ativos,
    (SELECT COUNT(*) FROM usuarios WHERE tipo = 0 AND ativo = 1 AND periodo IS NULL) AS alunos_sem_periodo,
    (SELECT COUNT(*) FROM modulos m WHERE NOT EXISTS (SELECT 1 FROM preceptores_modulos pm WHERE pm.idmodulo = m.idmodulo)) AS modulos_sem_preceptor,
    (SELECT COUNT(*) FROM modulos m WHERE NOT EXISTS (SELECT 1 FROM unidades_modulos um WHERE um.idmodulo = m.idmodulo)) AS modulos_sem_unidade");

$emAndamento = db_todos($conn, "SELECT c.idciclo, c.descricao, c.periodo, MIN(r.inicio) AS inicio, MAX(r.fim) AS fim
    FROM rodizio_ciclos c JOIN rodizios r ON r.idciclo = c.idciclo
    GROUP BY c.idciclo, c.descricao, c.periodo
    HAVING MAX(r.fim) >= CURDATE()
    ORDER BY inicio LIMIT 5");

$pendencias = array_filter([
    [$n['pendentes'], 'cadastro(s) aguardando aprovação', 'usuarios/usuarios.php?tipo=-1', 'bi-person-plus'],
    [$n['alunos_sem_periodo'], 'aluno(s) ativo(s) sem período definido — ficam fora dos rodízios', 'usuarios/usuarios.php?tipo=0&sem_periodo=1', 'bi-exclamation-triangle'],
    [$n['modulos_sem_preceptor'], 'módulo(s) sem preceptor', 'modulos/modulos.php', 'bi-person-badge'],
    [$n['modulos_sem_unidade'], 'módulo(s) sem unidade de saúde', 'unidades/unidades.php', 'bi-hospital'],
], fn($p) => (int) $p[0] > 0);

layout_inicio('Início');
page_header('Olá, ' . explode(' ', (string) $_SESSION['nome'])[0], 'Visão geral do internato.',
    '<a href="rodizios/gerar-rodizios.php" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Gerar rodízio</a>');
?>
<div class="stats">
    <?= stat_tile('Alunos ativos', (string) $n['alunos'], 'bi-mortarboard', 'listar-aluno.php') ?>
    <?= stat_tile('Preceptores ativos', (string) $n['preceptores'], 'bi-person-badge', 'listar-preceptor.php') ?>
    <?= stat_tile('Módulos', (string) $n['modulos'], 'bi-grid', 'modulos/modulos.php') ?>
    <?= stat_tile('Unidades de saúde', (string) $n['unidades'], 'bi-hospital', 'unidades/unidades.php') ?>
    <?= stat_tile('Rodízios em andamento', (string) $n['rodizios_ativos'], 'bi-arrow-repeat', 'rodizios/rodizios.php') ?>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2>Pendências</h2></div>
            <?php if (!$pendencias): ?>
                <div class="card-body"><?= empty_state('Tudo em ordem por aqui.', 'bi-check2-circle') ?></div>
            <?php else: ?>
                <ul class="check-list">
                    <?php foreach ($pendencias as [$qtd, $texto, $href, $icone]): ?>
                        <li><a class="d-flex align-items-center gap-3 px-4 py-3 text-reset text-decoration-none" href="<?= e($href) ?>">
                            <i class="bi <?= e($icone) ?> text-warning"></i>
                            <span><strong><?= (int) $qtd ?></strong> <?= e($texto) ?></span>
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2>Ciclos de rodízio vigentes</h2><a href="rodizios/rodizios.php" class="btn btn-sm btn-outline-secondary">Ver todos</a></div>
            <?php if (!$emAndamento): ?>
                <div class="card-body"><?= empty_state('Nenhum ciclo em andamento ou futuro.', 'bi-arrow-repeat') ?></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Ciclo</th><th>Período</th><th>Datas</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($emAndamento as $c): ?>
                                <tr>
                                    <td class="fw-medium"><?= e($c['descricao']) ?></td>
                                    <td><?= (int) $c['periodo'] ?>º</td>
                                    <td class="text-nowrap"><?= e(data_br($c['inicio'])) ?> – <?= e(data_br($c['fim'])) ?></td>
                                    <td><?= badge_status(status_periodo($c['inicio'], $c['fim'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php layout_fim(); ?>
