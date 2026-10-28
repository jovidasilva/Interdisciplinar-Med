<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$preceptor = db_um($conn, 'SELECT u.idusuario, u.nome, u.registro, pu.idunidade FROM usuarios u
    LEFT JOIN preceptores_unidades pu ON pu.idusuario = u.idusuario
    WHERE u.idusuario = ? AND u.tipo IN (1, 3)', 'i', [$id])
    ?? redirecionar('listar-preceptor.php', 'Preceptor não encontrado.', 'error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $idunidade = (int) ($_POST['idunidade'] ?? 0);
    $modulos = ids_do_post('modulos');

    $conn->begin_transaction();
    if ($idunidade) {
        db_exec($conn, 'INSERT INTO preceptores_unidades (idusuario, idunidade) SELECT ?, idunidade FROM unidades WHERE idunidade = ?
            ON DUPLICATE KEY UPDATE idunidade = VALUES(idunidade)', 'ii', [$id, $idunidade]);
    } else {
        db_exec($conn, 'DELETE FROM preceptores_unidades WHERE idusuario = ?', 'i', [$id]);
    }
    db_exec($conn, 'DELETE FROM preceptores_modulos WHERE idusuario = ?', 'i', [$id]);
    foreach ($modulos as $idmodulo) {
        db_exec($conn, 'INSERT INTO preceptores_modulos (idusuario, idmodulo) SELECT ?, idmodulo FROM modulos WHERE idmodulo = ?', 'ii', [$id, $idmodulo]);
    }
    $conn->commit();
    redirecionar('listar-preceptor.php', 'Preceptor atualizado.');
}

$unidades = db_todos($conn, 'SELECT idunidade, nome_unidade FROM unidades ORDER BY nome_unidade');
$modulos = db_todos($conn, 'SELECT m.idmodulo, m.nome_modulo, m.periodo,
        EXISTS (SELECT 1 FROM preceptores_modulos pm WHERE pm.idmodulo = m.idmodulo AND pm.idusuario = ?) AS marcado,
        (SELECT GROUP_CONCAT(um.idunidade) FROM unidades_modulos um WHERE um.idmodulo = m.idmodulo) AS unidades
    FROM modulos m ORDER BY m.periodo, m.nome_modulo', 'i', [$id]);

layout_inicio('Gerenciar preceptor');
page_header($preceptor['nome'], 'CRM ' . ($preceptor['registro'] ?: '—') . ' · Unidade e módulos sob sua preceptoria.', botao_voltar('listar-preceptor.php'));
?>
<form method="POST" style="max-width: 820px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="card">
        <div class="card-body">
            <label for="idunidade" class="form-label">Unidade de saúde</label>
            <select name="idunidade" id="idunidade" class="form-select" style="max-width: 420px">
                <option value="0">Sem unidade</option>
                <?php foreach ($unidades as $u): ?><option value="<?= (int) $u['idunidade'] ?>" <?= (int) $u['idunidade'] === (int) $preceptor['idunidade'] ? 'selected' : '' ?>><?= e($u['nome_unidade']) ?></option><?php endforeach; ?>
            </select>
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" id="soDaUnidade" checked>
                <label class="form-check-label" for="soDaUnidade">Mostrar só módulos oferecidos nessa unidade</label>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>Módulos</h2><span class="text-muted" id="contMod"></span></div>
        <?php if (!$modulos): ?>
            <div class="card-body"><?= empty_state('Nenhum módulo cadastrado.') ?></div>
        <?php else: ?>
            <ul class="check-list" id="listaModulos" style="max-height: 480px">
                <?php foreach ($modulos as $m): ?>
                    <li data-unidades=",<?= e($m['unidades']) ?>," <?= $m['marcado'] ? 'data-marcado' : '' ?>>
                        <label><input type="checkbox" class="form-check-input" name="modulos[]" value="<?= (int) $m['idmodulo'] ?>" <?= $m['marcado'] ? 'checked' : '' ?>>
                            <?= e($m['nome_modulo']) ?><small><?= (int) $m['periodo'] ?>º período</small></label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="form-actions">
        <a href="listar-preceptor.php" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Salvar</button>
    </div>
</form>
<script>
(function () {
    var sel = document.getElementById('idunidade'), so = document.getElementById('soDaUnidade');
    function filtrar() {
        var u = sel.value, vis = 0;
        document.querySelectorAll('#listaModulos li').forEach(function (li) {
            var marcado = li.querySelector('input').checked;
            var ok = !so.checked || u === '0' || marcado || li.dataset.unidades.indexOf(',' + u + ',') !== -1;
            li.hidden = !ok;
            if (ok) vis++;
        });
        document.getElementById('contMod').textContent = vis + ' exibido(s)';
    }
    sel.addEventListener('change', filtrar);
    so.addEventListener('change', filtrar);
    filtrar();
})();
</script>
<?php layout_fim(); ?>
