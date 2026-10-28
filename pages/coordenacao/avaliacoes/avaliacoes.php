<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$busca = trim((string) ($_GET['q'] ?? ''));
$idmodulo = (int) ($_GET['modulo'] ?? 0);
$idpreceptor = (int) ($_GET['preceptor'] ?? 0);
$like = '%' . $busca . '%';

$where = '(aluno.nome LIKE ? OR aluno.registro LIKE ?) AND (? = 0 OR a.idmodulo = ?) AND (? = 0 OR a.idpreceptor = ?)';
$params = [$like, $like, $idmodulo, $idmodulo, $idpreceptor, $idpreceptor];
$base = "FROM avaliacoes a
    JOIN usuarios aluno ON aluno.idusuario = a.idaluno
    JOIN usuarios preceptor ON preceptor.idusuario = a.idpreceptor
    JOIN modulos m ON m.idmodulo = a.idmodulo
    WHERE $where";

$resumo = db_um($conn, "SELECT COUNT(*) AS n, AVG(a.nota) AS media $base", 'ssiiii', $params);
[$pagina, $offset, $totalPaginas] = paginar((int) $resumo['n']);
$avaliacoes = db_todos($conn, "SELECT a.idavaliacao, a.nota, a.data_avaliacao, aluno.nome AS aluno, aluno.registro, preceptor.nome AS preceptor, m.nome_modulo
    $base ORDER BY a.data_avaliacao DESC, a.idavaliacao DESC LIMIT 20 OFFSET ?", 'ssiiiii', array_merge($params, [$offset]));

$modulos = db_todos($conn, 'SELECT idmodulo, nome_modulo, periodo FROM modulos ORDER BY periodo, nome_modulo');
$preceptores = db_todos($conn, 'SELECT idusuario, nome FROM usuarios WHERE tipo IN (1, 3) ORDER BY nome');

layout_inicio('Avaliações');
page_header('Avaliações', 'Avaliações registradas pelos preceptores.',
    '<a href="perguntas.php" class="btn btn-outline-secondary"><i class="bi bi-list-check"></i> Perguntas do formulário</a>');
?>
<div class="stats">
    <?= stat_tile('Avaliações no filtro', (string) $resumo['n'], 'bi-clipboard-check') ?>
    <?= stat_tile('Média no filtro', $resumo['media'] !== null ? number_format((float) $resumo['media'], 2, ',', '.') : '—', 'bi-graph-up') ?>
</div>
<form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
    <div class="input-group" style="max-width: 300px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Aluno ou RA" value="<?= e($busca) ?>">
    </div>
    <select name="modulo" class="form-select w-auto" aria-label="Módulo">
        <option value="0">Todos os módulos</option>
        <?php foreach ($modulos as $m): ?><option value="<?= (int) $m['idmodulo'] ?>" <?= (int) $m['idmodulo'] === $idmodulo ? 'selected' : '' ?>><?= e($m['nome_modulo']) ?> (<?= (int) $m['periodo'] ?>º)</option><?php endforeach; ?>
    </select>
    <select name="preceptor" class="form-select w-auto" aria-label="Preceptor">
        <option value="0">Todos os preceptores</option>
        <?php foreach ($preceptores as $p): ?><option value="<?= (int) $p['idusuario'] ?>" <?= (int) $p['idusuario'] === $idpreceptor ? 'selected' : '' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filtrar</button>
</form>
<div class="card">
    <?php if (!$avaliacoes): ?>
        <div class="card-body"><?= empty_state('Nenhuma avaliação encontrada.', 'bi-clipboard') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table align-middle">
                <thead><tr><th>Aluno</th><th>Módulo</th><th>Preceptor</th><th>Data</th><th class="text-end">Nota</th><th class="actions"></th></tr></thead>
                <tbody>
                    <?php foreach ($avaliacoes as $a): ?>
                        <tr>
                            <td><span class="fw-medium"><?= e($a['aluno']) ?></span><div class="small text-muted"><?= e($a['registro']) ?></div></td>
                            <td><?= e($a['nome_modulo']) ?></td>
                            <td><?= e($a['preceptor']) ?></td>
                            <td><?= e(data_br($a['data_avaliacao'])) ?></td>
                            <td class="text-end fw-semibold"><?= e(number_format((float) $a['nota'], 2, ',', '.')) ?></td>
                            <td class="actions"><a href="detalhes-avaliacoes.php?idavaliacao=<?= (int) $a['idavaliacao'] ?>" class="btn btn-sm btn-outline-secondary">Detalhes</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= paginacao($pagina, $totalPaginas) ?>
<?php layout_fim(); ?>
