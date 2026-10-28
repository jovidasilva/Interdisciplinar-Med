<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$id = usuario_id();
$diaHoje = DIAS_SEMANA[(int) date('N') - 1];

$resumo = db_um($conn, "SELECT
        (SELECT COUNT(*) FROM preceptores_modulos WHERE idusuario = ?) AS modulos,
        (SELECT COUNT(DISTINCT ma.idusuario) FROM preceptores_modulos pm JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo WHERE pm.idusuario = ?) AS alunos,
        (SELECT COUNT(*) FROM preceptores_modulos pm JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo
            WHERE pm.idusuario = ? AND NOT EXISTS (SELECT 1 FROM avaliacoes a WHERE a.idaluno = ma.idusuario AND a.idmodulo = ma.idmodulo AND a.idpreceptor = pm.idusuario)) AS pendentes",
    'iii', [$id, $id, $id]);

$horariosHoje = db_todos($conn, "SELECT h.hora_inicio, h.hora_fim, h.local, m.nome_modulo, u.nome_unidade, sg.nome_subgrupo
    FROM horarios h
    JOIN modulos m ON m.idmodulo = h.idmodulo
    JOIN unidades u ON u.idunidade = h.idunidade
    JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo
    WHERE h.idpreceptor = ? AND h.dia_semana = ?
    ORDER BY h.hora_inicio", 'is', [$id, $diaHoje]);

layout_inicio('Início');
page_header('Olá, ' . explode(' ', (string) $_SESSION['nome'])[0], 'Resumo dos seus módulos e atividades.');
?>
<div class="stats">
    <?= stat_tile('Módulos sob sua preceptoria', (string) (int) $resumo['modulos'], 'bi-grid', url('pages/preceptor/rodizios.php')) ?>
    <?= stat_tile('Alunos nos seus módulos', (string) (int) $resumo['alunos'], 'bi-mortarboard', url('pages/preceptor/listar-aluno.php')) ?>
    <?= stat_tile('Avaliações pendentes', (string) (int) $resumo['pendentes'], 'bi-clipboard-check', url('pages/preceptor/avaliacoes/avaliacoes.php')) ?>
    <?= stat_tile('Atividades hoje', (string) count($horariosHoje), 'bi-clock', url('pages/preceptor/horarios.php')) ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Hoje, <?= e($diaHoje) ?></h2>
        <a href="<?= e(url('pages/preceptor/horarios.php')) ?>" class="btn btn-sm btn-outline-secondary">Semana completa</a>
    </div>
    <?php if (!$horariosHoje): ?>
        <div class="card-body"><?= empty_state('Nenhuma atividade programada para hoje.', 'bi-calendar-check') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Horário</th><th>Módulo</th><th>Subgrupo</th><th>Unidade</th><th>Local</th></tr></thead>
                <tbody>
                    <?php foreach ($horariosHoje as $h): ?>
                        <tr>
                            <td class="time"><?= e(hora_curta($h['hora_inicio'])) ?>–<?= e(hora_curta($h['hora_fim'])) ?></td>
                            <td><?= e($h['nome_modulo']) ?></td>
                            <td><?= e($h['nome_subgrupo']) ?></td>
                            <td><?= e($h['nome_unidade']) ?></td>
                            <td><?= e($h['local'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
