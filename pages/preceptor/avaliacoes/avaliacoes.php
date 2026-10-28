<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$id = usuario_id();
$filtro = in_array($_GET['status'] ?? '', ['pendentes', 'realizadas', 'todas'], true) ? $_GET['status'] : 'pendentes';
$idmodulo = (int) ($_GET['modulo'] ?? 0);

$meusModulos = db_todos($conn, 'SELECT m.idmodulo, m.nome_modulo FROM preceptores_modulos pm JOIN modulos m ON m.idmodulo = pm.idmodulo WHERE pm.idusuario = ? ORDER BY m.nome_modulo', 'i', [$id]);

$condStatus = ['pendentes' => 'AND a.idavaliacao IS NULL', 'realizadas' => 'AND a.idavaliacao IS NOT NULL', 'todas' => ''][$filtro];
$pares = db_todos($conn, "SELECT u.idusuario, u.nome, u.registro, m.idmodulo, m.nome_modulo, a.idavaliacao, a.nota, a.data_avaliacao
    FROM preceptores_modulos pm
    JOIN modulos m ON m.idmodulo = pm.idmodulo
    JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo
    JOIN usuarios u ON u.idusuario = ma.idusuario AND u.ativo = 1
    LEFT JOIN avaliacoes a ON a.idaluno = u.idusuario AND a.idmodulo = m.idmodulo AND a.idpreceptor = pm.idusuario
    WHERE pm.idusuario = ? AND (? = 0 OR m.idmodulo = ?) $condStatus
    ORDER BY (a.idavaliacao IS NULL) DESC, m.nome_modulo, u.nome", 'iii', [$id, $idmodulo, $idmodulo]);

function link_filtro(array $mudar): string
{
    return '?' . http_build_query(array_merge($_GET, $mudar));
}

layout_inicio('Avaliações');
page_header('Avaliações', 'Avalie cada aluno uma vez por módulo sob sua preceptoria.');
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group" aria-label="Filtrar por status">
        <?php foreach (['pendentes' => 'Pendentes', 'realizadas' => 'Realizadas', 'todas' => 'Todas'] as $valor => $rotulo): ?>
            <a href="<?= e(link_filtro(['status' => $valor])) ?>" class="btn btn-sm <?= $filtro === $valor ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $rotulo ?></a>
        <?php endforeach; ?>
    </div>
    <form method="GET">
        <input type="hidden" name="status" value="<?= e($filtro) ?>">
        <select name="modulo" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filtrar por módulo">
            <option value="0">Todos os módulos</option>
            <?php foreach ($meusModulos as $m): ?><option value="<?= (int) $m['idmodulo'] ?>" <?= (int) $m['idmodulo'] === $idmodulo ? 'selected' : '' ?>><?= e($m['nome_modulo']) ?></option><?php endforeach; ?>
        </select>
    </form>
</div>

<div class="card">
    <?php if (!$meusModulos): ?>
        <div class="card-body"><?= empty_state('Você ainda não está associado a nenhum módulo. Fale com a coordenação.', 'bi-grid') ?></div>
    <?php elseif (!$pares): ?>
        <div class="card-body"><?= empty_state($filtro === 'pendentes' ? 'Nenhuma avaliação pendente. 🎉' : 'Nada encontrado com esse filtro.', 'bi-clipboard-check') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Aluno</th><th>RA</th><th>Módulo</th><th>Situação</th><th class="actions">Ação</th></tr></thead>
                <tbody>
                    <?php foreach ($pares as $p): ?>
                        <tr>
                            <td class="fw-medium"><?= e($p['nome']) ?></td>
                            <td><?= e($p['registro']) ?></td>
                            <td><?= e($p['nome_modulo']) ?></td>
                            <td>
                                <?php if ($p['idavaliacao']): ?>
                                    <span class="status status-success">Nota <?= e(number_format((float) $p['nota'], 2, ',', '.')) ?></span>
                                    <span class="small text-muted ms-1"><?= e(data_br($p['data_avaliacao'])) ?></span>
                                <?php else: ?>
                                    <?= badge_status('pendente') ?>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php if ($p['idavaliacao']): ?>
                                    <a href="consultar-avaliacao.php?id=<?= (int) $p['idavaliacao'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <?php else: ?>
                                    <a href="realizar-avaliacao.php?idaluno=<?= (int) $p['idusuario'] ?>&idmodulo=<?= (int) $p['idmodulo'] ?>" class="btn btn-sm btn-primary">Avaliar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
