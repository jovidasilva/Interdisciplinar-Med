<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_ALUNO);

$avaliacoes = db_todos($conn, "SELECT a.nota, a.data_avaliacao, m.nome_modulo, p.nome AS preceptor_nome
    FROM avaliacoes a
    JOIN modulos m ON a.idmodulo = m.idmodulo
    JOIN usuarios p ON a.idpreceptor = p.idusuario
    WHERE a.idaluno = ?
    ORDER BY a.data_avaliacao DESC", 'i', [usuario_id()]);

$total = count($avaliacoes);
$media = $total ? array_sum(array_column($avaliacoes, 'nota')) / $total : null;

layout_inicio('Minhas notas');
page_header('Minhas notas', 'Avaliações registradas pelos preceptores (escala de 0 a 10).');
?>
<div class="stats">
    <?= stat_tile('Média geral', $media !== null ? number_format($media, 2, ',', '.') : '—', 'bi-graph-up') ?>
    <?= stat_tile('Avaliações recebidas', (string) $total, 'bi-clipboard-check') ?>
</div>
<div class="card">
    <?php if (!$total): ?>
        <div class="card-body"><?= empty_state('Você ainda não recebeu nenhuma avaliação.', 'bi-clipboard') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Módulo</th><th>Preceptor</th><th>Data</th><th class="text-end">Nota</th></tr></thead>
                <tbody>
                    <?php foreach ($avaliacoes as $av): ?>
                        <tr>
                            <td><?= e($av['nome_modulo']) ?></td>
                            <td><?= e($av['preceptor_nome']) ?></td>
                            <td><?= e(data_br($av['data_avaliacao'])) ?></td>
                            <td class="text-end fw-semibold"><?= e(number_format((float) $av['nota'], 2, ',', '.')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
