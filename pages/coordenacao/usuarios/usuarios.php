<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$busca = trim((string) ($_GET['q'] ?? ''));
$tipo = isset($_GET['tipo']) && $_GET['tipo'] !== '' && array_key_exists((int) $_GET['tipo'], TIPOS_USUARIO) ? (int) $_GET['tipo'] : null;
$semPeriodo = !empty($_GET['sem_periodo']);

$where = '(nome LIKE ? OR login LIKE ? OR registro LIKE ? OR email LIKE ?)';
$tipos = 'ssss';
$like = '%' . $busca . '%';
$params = [$like, $like, $like, $like];
if ($tipo !== null) {
    $where .= ' AND tipo = ?';
    $tipos .= 'i';
    $params[] = $tipo;
}
if ($semPeriodo) {
    $where .= ' AND periodo IS NULL';
}

$total = (int) db_um($conn, "SELECT COUNT(*) AS n FROM usuarios WHERE $where", $tipos, $params)['n'];
[$pagina, $offset, $totalPaginas] = paginar($total, 25);
$usuarios = db_todos($conn, "SELECT idusuario, nome, login, email, tipo, registro, ativo, periodo FROM usuarios
    WHERE $where ORDER BY (tipo = -1) DESC, nome LIMIT 25 OFFSET ?", $tipos . 'i', array_merge($params, [$offset]));
$pendentes = (int) db_um($conn, 'SELECT COUNT(*) AS n FROM usuarios WHERE tipo = -1')['n'];

layout_inicio('Usuários');
page_header('Usuários', 'Aprove cadastros e ajuste tipo de acesso, período e status. As mudanças ficam pendentes até você salvar.',
    '<a href="importar-usuarios.php" class="btn btn-outline-secondary"><i class="bi bi-upload"></i> Importar CSV</a>');
?>
<form method="GET" class="d-flex flex-wrap gap-2 mb-3" role="search">
    <div class="input-group" style="max-width: 340px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Nome, login, RA/CRM ou e-mail" value="<?= e($busca) ?>">
    </div>
    <select name="tipo" class="form-select w-auto" onchange="this.form.submit()" aria-label="Filtrar por tipo">
        <option value="">Todos os tipos</option>
        <?php foreach (TIPOS_USUARIO as $v => $r): ?>
            <option value="<?= $v ?>" <?= $tipo === $v ? 'selected' : '' ?>><?= e($r) ?><?= $v === TIPO_PENDENTE && $pendentes ? " ({$pendentes})" : '' ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($semPeriodo): ?><input type="hidden" name="sem_periodo" value="1"><span class="status status-warning align-self-center">Sem período</span><?php endif; ?>
    <button class="btn btn-outline-secondary">Filtrar</button>
    <?php if ($busca !== '' || $tipo !== null || $semPeriodo): ?><a href="usuarios.php" class="btn btn-link">Limpar</a><?php endif; ?>
</form>

<div class="card">
    <?php if (!$usuarios): ?>
        <div class="card-body"><?= empty_state('Nenhum usuário encontrado.', 'bi-people') ?></div>
    <?php else: ?>
    <form id="formExcluir" method="POST" action="excluir-usuarios.php" data-require-check="usuarios[]"
          data-confirm="Excluir os usuários selecionados? Matrículas, grupos, horários e avaliações ligados a eles também serão removidos. Esta ação não pode ser desfeita.">
        <?= csrf_field() ?>
    </form>
    <div class="table-wrap">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 1%"><input type="checkbox" class="form-check-input" data-select-all="sel-usuario" aria-label="Selecionar todos"></th>
                    <th>Usuário</th>
                    <th>Registro</th>
                    <th style="min-width: 190px">Tipo</th>
                    <th style="min-width: 110px">Período</th>
                    <th>Ativo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): $tipoAtual = (int) $u['tipo']; ?>
                    <tr data-usuario-row data-id="<?= (int) $u['idusuario'] ?>" class="<?= $tipoAtual === TIPO_PENDENTE ? 'row-pending' : '' ?>">
                        <td>
                            <?php if ((int) $u['idusuario'] !== usuario_id()): ?>
                                <input type="checkbox" class="form-check-input sel-usuario" name="usuarios[]" value="<?= (int) $u['idusuario'] ?>" form="formExcluir" aria-label="Selecionar <?= e($u['nome']) ?>">
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fw-medium"><?= e($u['nome']) ?></span><?= (int) $u['idusuario'] === usuario_id() ? ' <span class="status status-info">você</span>' : '' ?>
                            <div class="small text-muted"><?= e($u['login']) ?> · <?= e($u['email'] ?: 'sem e-mail') ?></div>
                        </td>
                        <td><?= e($u['registro'] ?: '—') ?></td>
                        <td>
                            <select class="form-select form-select-sm campo" data-campo="tipo" data-original="<?= $tipoAtual ?>" aria-label="Tipo de acesso">
                                <?php foreach (TIPOS_USUARIO as $v => $r): ?><option value="<?= $v ?>" <?= $v === $tipoAtual ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm campo" data-campo="periodo" data-original="<?= e($u['periodo'] ?? '') ?>" aria-label="Período">
                                <option value="">—</option>
                                <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= (string) $p === (string) $u['periodo'] ? 'selected' : '' ?>><?= $p ?>º</option><?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input campo" type="checkbox" role="switch" data-campo="ativo" data-original="<?= (int) $u['ativo'] ?>" <?= (int) $u['ativo'] === 1 ? 'checked' : '' ?> aria-label="Ativo">
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <button type="submit" form="formExcluir" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Excluir selecionados</button>
        <form id="formSalvar" method="POST" action="salvar-usuarios.php" class="d-flex align-items-center gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="alteracoes" id="inputAlteracoes">
            <input type="hidden" name="voltar" value="<?= e($_SERVER['REQUEST_URI'] ?? 'usuarios.php') ?>">
            <span id="contadorAlteracoes" class="status status-info d-none"></span>
            <button type="submit" id="btnSalvar" class="btn btn-primary" disabled><i class="bi bi-check2"></i> Salvar alterações</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?= paginacao($pagina, $totalPaginas) ?>

<script>
(function () {
    var linhas = document.querySelectorAll('tr[data-usuario-row]');
    if (!linhas.length) return;
    function valor(el) { return el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value; }
    function alteradas() {
        var lista = [];
        linhas.forEach(function (row) {
            var campos = row.querySelectorAll('.campo'), mudou = false, item = { id: row.dataset.id };
            campos.forEach(function (c) { item[c.dataset.campo] = valor(c); if (valor(c) !== c.dataset.original) mudou = true; });
            row.classList.toggle('row-changed', mudou);
            if (mudou) lista.push(item);
        });
        return lista;
    }
    function atualizar() {
        var n = alteradas().length, badge = document.getElementById('contadorAlteracoes');
        document.getElementById('btnSalvar').disabled = n === 0;
        badge.classList.toggle('d-none', n === 0);
        badge.textContent = n + (n === 1 ? ' alteração pendente' : ' alterações pendentes');
    }
    document.querySelectorAll('.campo').forEach(function (c) { c.addEventListener('change', atualizar); });
    document.getElementById('formSalvar').addEventListener('submit', function () {
        document.getElementById('inputAlteracoes').value = JSON.stringify(alteradas());
    });
    // Avisa ao sair com alterações não salvas.
    window.addEventListener('beforeunload', function (ev) {
        if (alteradas().length && !document.getElementById('formSalvar').dataset.enviando) { ev.preventDefault(); ev.returnValue = ''; }
    });
    document.getElementById('formSalvar').addEventListener('submit', function () { this.dataset.enviando = '1'; });
})();
</script>
<?php layout_fim(); ?>
