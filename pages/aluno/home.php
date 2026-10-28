<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_ALUNO);

$id = usuario_id();
$hoje = date('Y-m-d');
$diaHoje = DIAS_SEMANA[(int) date('N') - 1];

$rodizioAtual = db_um($conn, "SELECT r.inicio, r.fim, m.nome_modulo
    FROM alunos_subgrupos asg
    JOIN rodizios_subgrupos rs ON rs.idsubgrupo = asg.idsubgrupo
    JOIN rodizios r ON r.idrodizio = rs.idrodizio
    JOIN modulos m ON m.idmodulo = r.idmodulo
    WHERE asg.idusuario = ? AND r.fim >= ?
    ORDER BY r.inicio LIMIT 1", 'is', [$id, $hoje]);

$horariosHoje = db_todos($conn, "SELECT h.hora_inicio, h.hora_fim, h.local, m.nome_modulo, u.nome_unidade
    FROM alunos_subgrupos asg
    JOIN horarios h ON h.idsubgrupo = asg.idsubgrupo
    JOIN modulos m ON m.idmodulo = h.idmodulo
    JOIN unidades u ON u.idunidade = h.idunidade
    WHERE asg.idusuario = ? AND h.dia_semana = ?
    ORDER BY h.hora_inicio", 'is', [$id, $diaHoje]);

$notas = db_um($conn, 'SELECT COUNT(*) AS total, AVG(nota) AS media FROM avaliacoes WHERE idaluno = ?', 'i', [$id]);

layout_inicio('Início');
page_header('Olá, ' . explode(' ', (string) $_SESSION['nome'])[0], 'Resumo do seu internato.');
?>
<div class="stats">
    <?= stat_tile('Rodízio ' . ($rodizioAtual && $rodizioAtual['inicio'] <= $hoje ? 'atual' : 'próximo'), $rodizioAtual['nome_modulo'] ?? '—', 'bi-arrow-repeat', 'rodizios.php') ?>
    <?= stat_tile('Atividades hoje (' . $diaHoje . ')', (string) count($horariosHoje), 'bi-clock', 'horarios.php') ?>
    <?= stat_tile('Avaliações recebidas', (string) (int) $notas['total'], 'bi-clipboard-check', 'notas.php') ?>
    <?= stat_tile('Média geral', $notas['media'] !== null ? number_format((float) $notas['media'], 2, ',', '.') : '—', 'bi-graph-up', 'notas.php') ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Hoje, <?= e($diaHoje) ?></h2>
        <a href="horarios.php" class="btn btn-sm btn-outline-secondary">Semana completa</a>
    </div>
    <?php if (!$horariosHoje): ?>
        <div class="card-body"><?= empty_state('Nenhuma atividade programada para hoje.', 'bi-calendar-check') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Horário</th><th>Módulo</th><th>Unidade</th><th>Local</th></tr></thead>
                <tbody>
                    <?php foreach ($horariosHoje as $h): ?>
                        <tr>
                            <td class="time"><?= e(hora_curta($h['hora_inicio'])) ?>–<?= e(hora_curta($h['hora_fim'])) ?></td>
                            <td><?= e($h['nome_modulo']) ?></td>
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
