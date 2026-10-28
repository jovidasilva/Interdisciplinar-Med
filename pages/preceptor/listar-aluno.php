<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$busca = trim((string) ($_GET['q'] ?? ''));
$like = '%' . $busca . '%';
$id = usuario_id();

// Só alunos matriculados em módulos deste preceptor.
$base = "FROM preceptores_modulos pm
    JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo
    JOIN usuarios u ON u.idusuario = ma.idusuario
    JOIN modulos m ON m.idmodulo = ma.idmodulo
    WHERE pm.idusuario = ? AND (u.nome LIKE ? OR u.registro LIKE ?)";

$total = (int) db_um($conn, "SELECT COUNT(DISTINCT u.idusuario) AS n $base", 'iss', [$id, $like, $like])['n'];
[$pagina, $offset, $totalPaginas] = paginar($total);

$alunos = db_todos($conn, "SELECT u.idusuario, u.nome, u.registro, u.email, u.periodo,
        GROUP_CONCAT(DISTINCT m.nome_modulo ORDER BY m.nome_modulo SEPARATOR '||') AS modulos,
        (SELECT AVG(a.nota) FROM avaliacoes a WHERE a.idaluno = u.idusuario AND a.idpreceptor = pm.idusuario) AS media
    $base
    GROUP BY u.idusuario, u.nome, u.registro, u.email, u.periodo, pm.idusuario
    ORDER BY u.nome LIMIT 20 OFFSET ?", 'issi', [$id, $like, $like, $offset]);

layout_inicio('Alunos');
page_header('Meus alunos', 'Alunos matriculados nos módulos sob sua preceptoria.');
?>
<form method="GET" class="mb-3" role="search">
    <div class="input-group" style="max-width: 420px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Buscar por nome ou RA" value="<?= e($busca) ?>">
    </div>
</form>
<div class="card">
    <?php if (!$alunos): ?>
        <div class="card-body"><?= empty_state($busca ? 'Nenhum aluno encontrado para essa busca.' : 'Nenhum aluno matriculado nos seus módulos.', 'bi-mortarboard') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Nome</th><th>RA</th><th>Período</th><th>Módulos</th><th class="text-end">Média (sua)</th></tr></thead>
                <tbody>
                    <?php foreach ($alunos as $a): ?>
                        <tr>
                            <td class="fw-medium"><?= e($a['nome']) ?><div class="small text-muted"><?= e($a['email']) ?></div></td>
                            <td><?= e($a['registro']) ?></td>
                            <td><?= $a['periodo'] ? (int) $a['periodo'] . 'º' : '—' ?></td>
                            <td><?php foreach (explode('||', (string) $a['modulos']) as $m): ?><span class="chip"><?= e($m) ?></span><?php endforeach; ?></td>
                            <td class="text-end"><?= $a['media'] !== null ? e(number_format((float) $a['media'], 2, ',', '.')) : '<span class="text-muted">—</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= paginacao($pagina, $totalPaginas) ?>
<?php layout_fim(); ?>
