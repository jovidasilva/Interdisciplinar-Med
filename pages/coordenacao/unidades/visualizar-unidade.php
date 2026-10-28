<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$idunidade = (int) ($_GET['idunidade'] ?? 0);
$unidade = db_um($conn, 'SELECT idunidade, nome_unidade, endereco_unidade FROM unidades WHERE idunidade = ?', 'i', [$idunidade])
    ?? redirecionar('unidades.php', 'Unidade não encontrada.', 'error');
$aba = in_array($_GET['aba'] ?? '', ['modulos', 'preceptores', 'departamentos'], true) ? $_GET['aba'] : 'modulos';

$modulosDentro = db_todos($conn, 'SELECT m.idmodulo, m.nome_modulo, m.periodo FROM unidades_modulos um JOIN modulos m ON m.idmodulo = um.idmodulo WHERE um.idunidade = ? ORDER BY m.periodo, m.nome_modulo', 'i', [$idunidade]);
$modulosFora = db_todos($conn, 'SELECT m.idmodulo, m.nome_modulo, m.periodo FROM modulos m WHERE NOT EXISTS (SELECT 1 FROM unidades_modulos um WHERE um.idmodulo = m.idmodulo AND um.idunidade = ?) ORDER BY m.periodo, m.nome_modulo', 'i', [$idunidade]);
$precDentro = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro FROM preceptores_unidades pu JOIN usuarios u ON u.idusuario = pu.idusuario WHERE pu.idunidade = ? ORDER BY u.nome', 'i', [$idunidade]);
$precFora = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro, un.nome_unidade FROM usuarios u
    LEFT JOIN preceptores_unidades pu ON pu.idusuario = u.idusuario LEFT JOIN unidades un ON un.idunidade = pu.idunidade
    WHERE u.tipo IN (1, 3) AND u.ativo = 1 AND (pu.idunidade IS NULL OR pu.idunidade <> ?) ORDER BY (pu.idunidade IS NOT NULL), u.nome', 'i', [$idunidade]);
$departamentos = db_todos($conn, 'SELECT d.iddepartamento, d.nome_departamento,
        (SELECT GROUP_CONCAT(md.idmodulo) FROM modulos_departamentos md WHERE md.iddepartamento = d.iddepartamento) AS modulos
    FROM departamentos d WHERE d.idunidade = ? ORDER BY d.nome_departamento', 'i', [$idunidade]);

/** Card de lista com checkboxes que posta uma ação para acoes-unidades.php. */
function lista_assoc(string $titulo, array $itens, string $campo, string $acao, string $botao, string $classeBotao, int $idunidade, string $aba, callable $rotulo, string $vazio): void
{
    $sel = 'sel-' . $acao;
    ?>
    <form method="POST" action="acoes-unidades.php" class="card" data-require-check="<?= e($campo) ?>[]">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="<?= e($acao) ?>">
        <input type="hidden" name="idunidade" value="<?= $idunidade ?>">
        <input type="hidden" name="aba" value="<?= e($aba) ?>">
        <div class="card-header">
            <h2><?= e($titulo) ?> <span class="text-muted fw-normal">(<?= count($itens) ?>)</span></h2>
            <?php if ($itens): ?><label class="small"><input type="checkbox" class="form-check-input me-1" data-select-all="<?= e($sel) ?>"> Todos</label><?php endif; ?>
        </div>
        <?php if (!$itens): ?>
            <div class="card-body"><?= empty_state($vazio) ?></div>
        <?php else: ?>
            <ul class="check-list">
                <?php foreach ($itens as $i): ?>
                    <li><label><input type="checkbox" class="form-check-input <?= e($sel) ?>" name="<?= e($campo) ?>[]" value="<?= (int) ($i['idmodulo'] ?? $i['idusuario']) ?>"> <?= $rotulo($i) ?></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer"><span></span><button class="btn btn-sm <?= e($classeBotao) ?>"><?= e($botao) ?></button></div>
        <?php endif; ?>
    </form>
    <?php
}

$rotuloModulo = fn($m) => e($m['nome_modulo']) . '<small>' . (int) $m['periodo'] . 'º período</small>';

layout_inicio($unidade['nome_unidade']);
page_header($unidade['nome_unidade'], $unidade['endereco_unidade'] ?: 'Sem endereço cadastrado',
    botao_voltar('unidades.php')
    . '<a href="editar-unidades.php?idunidade=' . $idunidade . '" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Editar</a>'
    . form_acao('acoes-unidades.php', ['acao' => 'excluir', 'idunidade' => $idunidade], '<i class="bi bi-trash"></i> Excluir', 'btn btn-outline-danger',
        'Excluir esta unidade? Departamentos e associações dela também serão removidos.'));
?>
<ul class="nav nav-underline mb-4">
    <?php foreach (['modulos' => 'Módulos (' . count($modulosDentro) . ')', 'preceptores' => 'Preceptores (' . count($precDentro) . ')', 'departamentos' => 'Departamentos (' . count($departamentos) . ')'] as $k => $r): ?>
        <li class="nav-item"><a class="nav-link<?= $aba === $k ? ' active' : '' ?>" href="?idunidade=<?= $idunidade ?>&aba=<?= $k ?>"<?= $aba === $k ? ' aria-current="page"' : '' ?>><?= e($r) ?></a></li>
    <?php endforeach; ?>
</ul>

<?php if ($aba === 'modulos'): ?>
    <div class="assoc">
        <?php lista_assoc('Oferecidos nesta unidade', $modulosDentro, 'modulos', 'desassociar_modulos', 'Remover da unidade', 'btn-outline-danger', $idunidade, $aba, $rotuloModulo, 'Nenhum módulo associado.'); ?>
        <?php lista_assoc('Outros módulos', $modulosFora, 'modulos', 'associar_modulos', 'Associar à unidade', 'btn-primary', $idunidade, $aba, $rotuloModulo, 'Todos os módulos já estão nesta unidade.'); ?>
    </div>
<?php elseif ($aba === 'preceptores'): ?>
    <div class="assoc">
        <?php lista_assoc('Preceptores desta unidade', $precDentro, 'preceptores', 'desassociar_preceptores', 'Remover da unidade', 'btn-outline-danger', $idunidade, $aba,
            fn($p) => e($p['nome']) . '<small>' . e($p['registro']) . '</small>', 'Nenhum preceptor associado.'); ?>
        <?php lista_assoc('Outros preceptores', $precFora, 'preceptores', 'associar_preceptores', 'Trazer para esta unidade', 'btn-primary', $idunidade, $aba,
            fn($p) => e($p['nome']) . '<small>' . ($p['nome_unidade'] ? 'em ' . e($p['nome_unidade']) : 'sem unidade') . '</small>', 'Não há outros preceptores ativos.'); ?>
    </div>
    <p class="small text-muted mt-3"><i class="bi bi-info-circle"></i> Cada preceptor pertence a uma unidade; trazê-lo para cá o remove da unidade anterior. Os módulos de cada preceptor são definidos em <a href="../listar-preceptor.php">Preceptores</a>.</p>
<?php else: ?>
    <form method="POST" action="acoes-unidades.php" class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-2 align-items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="acao" value="adicionar_departamento">
            <input type="hidden" name="idunidade" value="<?= $idunidade ?>">
            <input type="hidden" name="aba" value="departamentos">
            <div class="flex-grow-1" style="max-width: 420px">
                <label for="nome_departamento" class="form-label">Novo departamento</label>
                <input type="text" name="nome_departamento" id="nome_departamento" class="form-control" maxlength="100" required placeholder="Ex.: Pronto-socorro">
            </div>
            <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Adicionar</button>
        </div>
    </form>
    <?php if (!$departamentos): ?>
        <div class="card"><div class="card-body"><?= empty_state('Nenhum departamento cadastrado nesta unidade.', 'bi-diagram-3') ?></div></div>
    <?php endif; ?>
    <div class="row g-3">
        <?php foreach ($departamentos as $d): $marcados = array_map('intval', explode(',', (string) $d['modulos'])); ?>
            <div class="col-lg-6">
                <form method="POST" action="acoes-unidades.php" class="card h-100">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="departamento_modulos">
                    <input type="hidden" name="idunidade" value="<?= $idunidade ?>">
                    <input type="hidden" name="iddepartamento" value="<?= (int) $d['iddepartamento'] ?>">
                    <input type="hidden" name="aba" value="departamentos">
                    <div class="card-header">
                        <h2><?= e($d['nome_departamento']) ?></h2>
                        <button type="submit" form="excluirDep<?= (int) $d['iddepartamento'] ?>" class="btn btn-sm btn-outline-danger" aria-label="Excluir departamento"><i class="bi bi-trash"></i></button>
                    </div>
                    <?php if (!$modulosDentro): ?>
                        <div class="card-body text-muted small">Associe módulos à unidade para distribuí-los entre os departamentos.</div>
                    <?php else: ?>
                        <ul class="check-list">
                            <?php foreach ($modulosDentro as $m): ?>
                                <li><label><input type="checkbox" class="form-check-input" name="modulos[]" value="<?= (int) $m['idmodulo'] ?>" <?= in_array((int) $m['idmodulo'], $marcados, true) ? 'checked' : '' ?>> <?= $rotuloModulo($m) ?></label></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer"><span class="small text-muted">Módulos atendidos neste departamento</span><button class="btn btn-sm btn-primary">Salvar</button></div>
                    <?php endif; ?>
                </form>
                <form id="excluirDep<?= (int) $d['iddepartamento'] ?>" method="POST" action="acoes-unidades.php" data-confirm="Excluir o departamento &quot;<?= e($d['nome_departamento']) ?>&quot;?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="excluir_departamento">
                    <input type="hidden" name="idunidade" value="<?= $idunidade ?>">
                    <input type="hidden" name="iddepartamento" value="<?= (int) $d['iddepartamento'] ?>">
                    <input type="hidden" name="aba" value="departamentos">
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php layout_fim(); ?>
