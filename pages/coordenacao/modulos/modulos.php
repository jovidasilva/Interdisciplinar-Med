<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$periodo = in_array((int) ($_GET['periodo'] ?? 0), PERIODOS, true) ? (int) $_GET['periodo'] : 0;

$modulos = db_todos($conn, "SELECT m.idmodulo, m.nome_modulo, m.periodo,
        (SELECT COUNT(*) FROM modulos_alunos ma WHERE ma.idmodulo = m.idmodulo) AS alunos,
        (SELECT GROUP_CONCAT(u.nome_unidade ORDER BY u.nome_unidade SEPARATOR '||') FROM unidades_modulos um JOIN unidades u ON u.idunidade = um.idunidade WHERE um.idmodulo = m.idmodulo) AS unidades,
        (SELECT GROUP_CONCAT(u.nome ORDER BY u.nome SEPARATOR '||') FROM preceptores_modulos pm JOIN usuarios u ON u.idusuario = pm.idusuario WHERE pm.idmodulo = m.idmodulo) AS preceptores
    FROM modulos m
    WHERE (? = 0 OR m.periodo = ?)
    ORDER BY m.periodo, m.nome_modulo", 'ii', [$periodo, $periodo]);

function chips(?string $lista, string $vazio): string
{
    if (!$lista) {
        return '<span class="status status-warning">' . e($vazio) . '</span>';
    }
    return implode('', array_map(fn($x) => '<span class="chip">' . e($x) . '</span>', explode('||', $lista)));
}

layout_inicio('Módulos');
page_header('Módulos', 'Disciplinas do internato, por período. Unidades e preceptores são definidos nas telas de Unidades e Preceptores.',
    '<a href="importar-modulos.php" class="btn btn-outline-secondary"><i class="bi bi-upload"></i> Importar CSV</a>'
    . '<a href="editar-modulos.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo módulo</a>');
?>
<form method="GET" class="d-flex gap-2 align-items-center mb-3">
    <label for="periodo" class="form-label mb-0 text-muted">Período</label>
    <select name="periodo" id="periodo" class="form-select w-auto" onchange="this.form.submit()">
        <option value="0">Todos</option>
        <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= $p === $periodo ? 'selected' : '' ?>><?= $p ?>º período</option><?php endforeach; ?>
    </select>
</form>
<div class="card">
    <?php if (!$modulos): ?>
        <div class="card-body"><?= empty_state('Nenhum módulo cadastrado.', 'bi-grid', '<a href="editar-modulos.php" class="btn btn-primary">Cadastrar módulo</a>') ?></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table align-middle">
                <thead><tr><th>Módulo</th><th>Período</th><th class="text-end">Alunos</th><th>Unidades</th><th>Preceptores</th><th class="actions">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($modulos as $m): ?>
                        <tr>
                            <td class="fw-medium"><a href="visualizar-modulos.php?idmodulo=<?= (int) $m['idmodulo'] ?>"><?= e($m['nome_modulo']) ?></a></td>
                            <td><?= (int) $m['periodo'] ?>º</td>
                            <td class="text-end"><?= (int) $m['alunos'] ?></td>
                            <td><?= chips($m['unidades'], 'sem unidade') ?></td>
                            <td><?= chips($m['preceptores'], 'sem preceptor') ?></td>
                            <td class="actions">
                                <a href="visualizar-modulos.php?idmodulo=<?= (int) $m['idmodulo'] ?>" class="btn btn-sm btn-outline-secondary">Alunos</a>
                                <a href="editar-modulos.php?idmodulo=<?= (int) $m['idmodulo'] ?>" class="btn btn-sm btn-outline-secondary" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                                <?= form_acao('acoes-modulos.php', ['acao' => 'excluir', 'idmodulo' => $m['idmodulo']], '<i class="bi bi-trash"></i>', 'btn btn-sm btn-outline-danger',
                                    'Excluir o módulo "' . $m['nome_modulo'] . '"? Matrículas, associações e avaliações dele serão removidas.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php layout_fim(); ?>
