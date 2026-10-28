<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$busca = trim((string) ($_GET['q'] ?? ''));
$like = '%' . $busca . '%';

$total = (int) db_um($conn, 'SELECT COUNT(*) AS n FROM usuarios WHERE tipo IN (1, 3) AND (nome LIKE ? OR registro LIKE ?)', 'ss', [$like, $like])['n'];
[$pagina, $offset, $totalPaginas] = paginar($total);

$preceptores = db_todos($conn, "SELECT u.idusuario, u.nome, u.registro, u.email, u.telefone, u.ativo, un.nome_unidade,
        (SELECT GROUP_CONCAT(m.nome_modulo ORDER BY m.nome_modulo SEPARATOR '||') FROM preceptores_modulos pm JOIN modulos m ON m.idmodulo = pm.idmodulo WHERE pm.idusuario = u.idusuario) AS modulos
    FROM usuarios u
    LEFT JOIN preceptores_unidades pu ON pu.idusuario = u.idusuario
    LEFT JOIN unidades un ON un.idunidade = pu.idunidade
    WHERE u.tipo IN (1, 3) AND (u.nome LIKE ? OR u.registro LIKE ?)
    ORDER BY u.nome LIMIT 20 OFFSET ?", 'ssi', [$like, $like, $offset]);

layout_inicio('Preceptores');
page_header('Preceptores', 'Defina a unidade e os módulos de cada preceptor. Novos preceptores entram pela tela de Usuários.');
?>
<form method="GET" class="mb-3" role="search">
    <div class="input-group" style="max-width: 420px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Buscar por nome ou CRM" value="<?= e($busca) ?>">
    </div>
</form>
<div class="card">
    <?php if (!$preceptores): ?>
        <div class="card-body"><?= empty_state('Nenhum preceptor encontrado.', 'bi-person-badge') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table align-middle">
                <thead><tr><th>Preceptor</th><th>CRM</th><th>Unidade</th><th>Módulos</th><th>Status</th><th class="actions"></th></tr></thead>
                <tbody>
                    <?php foreach ($preceptores as $p): ?>
                        <tr>
                            <td><span class="fw-medium"><?= e($p['nome']) ?></span><div class="small text-muted"><?= e($p['email'] ?: '—') ?> · <?= e($p['telefone'] ?: '—') ?></div></td>
                            <td><?= e($p['registro'] ?: '—') ?></td>
                            <td><?= $p['nome_unidade'] ? e($p['nome_unidade']) : '<span class="status status-warning">sem unidade</span>' ?></td>
                            <td><?= $p['modulos'] ? implode('', array_map(fn($m) => '<span class="chip">' . e($m) . '</span>', explode('||', $p['modulos']))) : '<span class="status status-warning">nenhum</span>' ?></td>
                            <td><?= badge_status((int) $p['ativo'] ? 'ativo' : 'inativo') ?></td>
                            <td class="actions"><a href="associar-preceptor.php?id=<?= (int) $p['idusuario'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-sliders"></i> Gerenciar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= paginacao($pagina, $totalPaginas) ?>
<?php layout_fim(); ?>
