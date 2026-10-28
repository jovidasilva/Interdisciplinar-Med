<?php
// Detalhe de uma avaliação, usado pelo preceptor (só as próprias) e pela
// coordenação (todas). Retorna null se não existir / não for permitido.

function carregar_avaliacao(mysqli $conn, int $idavaliacao, ?int $somenteDoPreceptor = null): ?array
{
    $avaliacao = db_um($conn, "SELECT a.idavaliacao, a.idaluno, a.idmodulo, a.nota, a.data_avaliacao, a.idpreceptor,
            aluno.nome AS aluno, aluno.registro, preceptor.nome AS preceptor, m.nome_modulo,
            (SELECT GROUP_CONCAT(u.nome_unidade SEPARATOR ', ') FROM unidades_modulos um JOIN unidades u ON u.idunidade = um.idunidade WHERE um.idmodulo = m.idmodulo) AS unidades
        FROM avaliacoes a
        JOIN usuarios aluno ON aluno.idusuario = a.idaluno
        JOIN usuarios preceptor ON preceptor.idusuario = a.idpreceptor
        JOIN modulos m ON m.idmodulo = a.idmodulo
        WHERE a.idavaliacao = ?", 'i', [$idavaliacao]);

    if (!$avaliacao || ($somenteDoPreceptor !== null && (int) $avaliacao['idpreceptor'] !== $somenteDoPreceptor)) {
        return null;
    }

    // Rodízio do PRÓPRIO aluno nesse módulo (antes pegava qualquer rodízio do módulo).
    $avaliacao['rodizio'] = db_um($conn, "SELECT r.inicio, r.fim FROM rodizios r
        JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
        JOIN alunos_subgrupos asg ON asg.idsubgrupo = rs.idsubgrupo
        WHERE r.idmodulo = ? AND asg.idusuario = ?
        ORDER BY ABS(DATEDIFF(r.inicio, ?)) LIMIT 1", 'iis', [$avaliacao['idmodulo'], $avaliacao['idaluno'], $avaliacao['data_avaliacao']]);

    $avaliacao['respostas'] = db_todos($conn, 'SELECT p.titulo, p.descricao, ar.resposta
        FROM avaliacoes_respostas ar JOIN perguntas_avaliacoes p ON p.idpergunta = ar.idpergunta
        WHERE ar.idavaliacao = ? ORDER BY p.idpergunta', 'i', [$idavaliacao]);

    return $avaliacao;
}

function render_avaliacao(array $a): void
{
    ?>
    <div class="card">
        <div class="card-body">
            <dl class="detail-list">
                <div><dt>Aluno</dt><dd><?= e($a['aluno']) ?> <span class="text-muted">· <?= e($a['registro']) ?></span></dd></div>
                <div><dt>Módulo</dt><dd><?= e($a['nome_modulo']) ?></dd></div>
                <div><dt>Preceptor</dt><dd><?= e($a['preceptor']) ?></dd></div>
                <div><dt>Unidade</dt><dd><?= e($a['unidades'] ?: '—') ?></dd></div>
                <div><dt>Rodízio</dt><dd><?= $a['rodizio'] ? e(data_br($a['rodizio']['inicio']) . ' – ' . data_br($a['rodizio']['fim'])) : '—' ?></dd></div>
                <div><dt>Data</dt><dd><?= e(data_br($a['data_avaliacao'])) ?></dd></div>
                <div><dt>Nota final</dt><dd class="fs-4 fw-bold"><?= e(number_format((float) $a['nota'], 2, ',', '.')) ?></dd></div>
            </dl>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>Respostas</h2></div>
        <?php if (!$a['respostas']): ?>
            <div class="card-body"><?= empty_state('Nenhuma resposta registrada.') ?></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Critério</th><th>Resposta</th></tr></thead>
                    <tbody>
                        <?php foreach ($a['respostas'] as $r): $v = (int) $r['resposta']; ?>
                            <tr>
                                <td><span class="fw-medium"><?= e($r['titulo']) ?></span><div class="small text-muted"><?= e($r['descricao']) ?></div></td>
                                <td class="text-nowrap"><span class="status status-<?= $v >= 8 ? 'success' : ($v >= 6 ? 'info' : 'warning') ?>"><?= e(OPCOES_RESPOSTA[$v] ?? $r['resposta']) ?> (<?= $v ?>)</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
