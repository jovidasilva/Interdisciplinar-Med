<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$busca = trim((string) ($_GET['q'] ?? ''));
$periodo = in_array((int) ($_GET['periodo'] ?? 0), PERIODOS, true) ? (int) $_GET['periodo'] : 0;
$like = '%' . $busca . '%';
$filtro = 'u.tipo = 0 AND (u.nome LIKE ? OR u.registro LIKE ? OR u.email LIKE ?) AND (? = 0 OR u.periodo = ?)';
$params = [$like, $like, $like, $periodo, $periodo];

$total = (int) db_um($conn, "SELECT COUNT(*) AS n FROM usuarios u WHERE $filtro", 'sssii', $params)['n'];
[$pagina, $offset, $totalPaginas] = paginar($total);

// Subgrupo do ciclo mais recente do aluno e média das avaliações.
$alunos = db_todos($conn, "SELECT u.idusuario, u.nome, u.registro, u.email, u.telefone, u.ativo, u.periodo,
        (SELECT sg.nome_subgrupo FROM alunos_subgrupos asg JOIN subgrupos sg ON sg.idsubgrupo = asg.idsubgrupo JOIN grupos g ON g.idgrupo = sg.idgrupo
            WHERE asg.idusuario = u.idusuario ORDER BY g.idciclo DESC LIMIT 1) AS subgrupo,
        (SELECT AVG(a.nota) FROM avaliacoes a WHERE a.idaluno = u.idusuario) AS media
    FROM usuarios u WHERE $filtro ORDER BY u.nome LIMIT 20 OFFSET ?", 'sssiii', array_merge($params, [$offset]));

layout_inicio('Alunos');
page_header('Alunos', 'Situação dos alunos no internato. Para aprovar, mudar período ou desativar, use a tela de Usuários.',
    '<a href="usuarios/usuarios.php?tipo=0" class="btn btn-outline-secondary"><i class="bi bi-person-gear"></i> Editar em Usuários</a>');
?>
<form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
    <div class="input-group" style="max-width: 360px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Nome, RA ou e-mail" value="<?= e($busca) ?>">
    </div>
    <select name="periodo" class="form-select w-auto" onchange="this.form.submit()" aria-label="Período">
        <option value="0">Todos os períodos</option>
        <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= $p === $periodo ? 'selected' : '' ?>><?= $p ?>º período</option><?php endforeach; ?>
    </select>
</form>
<div class="card">
    <?php if (!$alunos): ?>
        <div class="card-body"><?= empty_state('Nenhum aluno encontrado.', 'bi-mortarboard') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Aluno</th><th>RA</th><th>Período</th><th>Subgrupo atual</th><th class="text-end">Média</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($alunos as $a): ?>
                        <tr>
                            <td><span class="fw-medium"><?= e($a['nome']) ?></span><div class="small text-muted"><?= e($a['email'] ?: '—') ?> · <?= e($a['telefone'] ?: '—') ?></div></td>
                            <td><?= e($a['registro'] ?: '—') ?></td>
                            <td><?= $a['periodo'] ? (int) $a['periodo'] . 'º' : '<span class="status status-warning">sem período</span>' ?></td>
                            <td><?= $a['subgrupo'] ? '<span class="chip">' . e($a['subgrupo']) . '</span>' : '<span class="text-muted">—</span>' ?></td>
                            <td class="text-end"><?= $a['media'] !== null ? e(number_format((float) $a['media'], 2, ',', '.')) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= badge_status((int) $a['ativo'] ? 'ativo' : 'inativo') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= paginacao($pagina, $totalPaginas) ?>
<?php layout_fim(); ?>
