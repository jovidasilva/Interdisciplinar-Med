<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$unidades = db_todos($conn, "SELECT u.idunidade, u.nome_unidade, u.endereco_unidade,
        (SELECT COUNT(*) FROM departamentos d WHERE d.idunidade = u.idunidade) AS departamentos,
        (SELECT COUNT(*) FROM unidades_modulos um WHERE um.idunidade = u.idunidade) AS modulos,
        (SELECT COUNT(*) FROM preceptores_unidades pu WHERE pu.idunidade = u.idunidade) AS preceptores
    FROM unidades u ORDER BY u.nome_unidade");

layout_inicio('Unidades');
page_header('Unidades de saúde', 'Locais onde acontece o internato, com seus departamentos, módulos e preceptores.',
    '<a href="editar-unidades.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nova unidade</a>');
?>
<?php if (!$unidades): ?>
    <div class="card"><div class="card-body"><?= empty_state('Nenhuma unidade cadastrada.', 'bi-hospital', '<a href="editar-unidades.php" class="btn btn-primary">Cadastrar unidade</a>') ?></div></div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($unidades as $u): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="stat-icon"><i class="bi bi-hospital"></i></span>
                            <div class="min-w-0">
                                <h2 class="h6 mb-1"><a href="visualizar-unidade.php?idunidade=<?= (int) $u['idunidade'] ?>" class="stretched-link text-reset text-decoration-none"><?= e($u['nome_unidade']) ?></a></h2>
                                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($u['endereco_unidade'] ?: 'Sem endereço') ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap mt-auto">
                            <span class="chip"><?= (int) $u['modulos'] ?> módulo(s)</span>
                            <span class="chip"><?= (int) $u['preceptores'] ?> preceptor(es)</span>
                            <span class="chip"><?= (int) $u['departamentos'] ?> departamento(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php layout_fim(); ?>
