<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/rodizios.php';
exigir_acesso(PAPEIS_COORDENACAO);

$periodo = in_array((int) ($_GET['periodo'] ?? 0), PERIODOS, true) ? (int) $_GET['periodo'] : 0;
$anterior = $_SESSION['form_rodizio'] ?? [];
unset($_SESSION['form_rodizio']);

$modulos = $alunosAtivos = [];
if ($periodo) {
    $modulos = db_todos($conn, "SELECT m.idmodulo, m.nome_modulo,
            (SELECT GROUP_CONCAT(u.nome_unidade SEPARATOR ', ') FROM unidades_modulos um JOIN unidades u ON u.idunidade = um.idunidade WHERE um.idmodulo = m.idmodulo) AS unidades,
            (SELECT COUNT(*) FROM preceptores_modulos pm WHERE pm.idmodulo = m.idmodulo) AS preceptores
        FROM modulos m WHERE m.periodo = ? ORDER BY m.nome_modulo", 'i', [$periodo]);
    $alunosAtivos = db_um($conn, 'SELECT COUNT(*) AS n FROM usuarios WHERE tipo = 0 AND ativo = 1 AND periodo = ?', 'i', [$periodo])['n'];
}
$selecionados = $anterior['modulos'] ?? [];

layout_inicio('Gerar ciclo de rodízio');
page_header('Gerar ciclo de rodízio', 'Os alunos ativos do período são divididos em um grupo por módulo; cada grupo passa por todos os módulos, um por janela.', botao_voltar('rodizios.php'));
?>
<form method="GET" class="card mb-4">
    <div class="card-body d-flex flex-wrap gap-3 align-items-end">
        <div>
            <label for="periodo" class="form-label">1. Período</label>
            <select name="periodo" id="periodo" class="form-select" onchange="this.form.submit()" required>
                <option value="">Selecione…</option>
                <?php foreach (PERIODOS as $p): ?><option value="<?= $p ?>" <?= $p === $periodo ? 'selected' : '' ?>><?= $p ?>º período</option><?php endforeach; ?>
            </select>
        </div>
        <?php if ($periodo): ?>
            <p class="mb-2 text-muted"><i class="bi bi-mortarboard"></i> <strong><?= (int) $alunosAtivos ?></strong> aluno(s) ativo(s) no <?= $periodo ?>º período</p>
        <?php endif; ?>
        <noscript><button class="btn btn-outline-secondary">Carregar</button></noscript>
    </div>
</form>

<?php if ($periodo && !$modulos): ?>
    <div class="card"><div class="card-body"><?= empty_state("Nenhum módulo cadastrado para o {$periodo}º período.", 'bi-grid', '<a href="../modulos/modulos.php" class="btn btn-outline-primary">Cadastrar módulos</a>') ?></div></div>
<?php elseif ($periodo): ?>
<form method="POST" action="acoes-rodizios.php" id="formCiclo">
    <?= csrf_field() ?>
    <input type="hidden" name="acao" value="gerar">
    <input type="hidden" name="periodo" value="<?= $periodo ?>">

    <div class="card">
        <div class="card-header"><h2>2. Módulos do ciclo</h2><span class="text-muted">Escolha de <?= RODIZIO_MIN_MODULOS ?> a <?= RODIZIO_MAX_MODULOS ?> — um grupo e uma janela por módulo</span></div>
        <ul class="check-list">
            <?php foreach ($modulos as $m): ?>
                <li>
                    <label>
                        <input type="checkbox" class="form-check-input modulo-check" name="modulos[]" value="<?= (int) $m['idmodulo'] ?>" data-nome="<?= e($m['nome_modulo']) ?>"
                            <?= in_array((int) $m['idmodulo'], $selecionados, true) ? 'checked' : '' ?>>
                        <span class="fw-medium"><?= e($m['nome_modulo']) ?></span>
                        <small>
                            <?= $m['unidades'] ? e($m['unidades']) : '<span class="text-warning">sem unidade</span>' ?> ·
                            <?= (int) $m['preceptores'] ?: '<span class="text-warning">0</span>' ?> preceptor(es)
                        </small>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><h2>3. Janelas de datas</h2><span class="text-muted">Consecutivas e sem sobreposição</span></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-end mb-3 pb-3 border-bottom">
                <div>
                    <label class="form-label" for="autoInicio">Início do ciclo</label>
                    <input type="date" id="autoInicio" class="form-control">
                </div>
                <div>
                    <label class="form-label" for="autoSemanas">Semanas por janela</label>
                    <input type="number" id="autoSemanas" class="form-control" min="1" max="52" value="8" style="width: 120px">
                </div>
                <button type="button" class="btn btn-outline-primary" id="btnAutoDatas"><i class="bi bi-magic"></i> Preencher datas</button>
            </div>
            <div id="janelas"><p class="text-muted mb-0">Selecione os módulos para definir as janelas.</p></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>4. Grupos</h2></div>
        <div class="card-body form-grid">
            <div>
                <label for="subgrupos" class="form-label">Subgrupos por grupo</label>
                <input type="number" name="subgrupos" id="subgrupos" class="form-control" min="1" max="<?= RODIZIO_MAX_SUBGRUPOS ?>" value="<?= (int) ($anterior['subgrupos'] ?? 3) ?>" required>
                <div class="form-text">Os alunos são sorteados e distribuídos de forma equilibrada.</div>
            </div>
            <div>
                <label for="descricao" class="form-label">Nome do ciclo</label>
                <input type="text" name="descricao" id="descricao" class="form-control" maxlength="90" value="<?= e($anterior['descricao'] ?? $periodo . 'º período — ' . date('Y') . '.' . (date('n') <= 6 ? 1 : 2)) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Prévia da rotação</h2><span class="text-muted" id="resumoPrevia"></span></div>
        <div class="table-wrap" id="previa"><div class="card-body text-muted">Selecione ao menos <?= RODIZIO_MIN_MODULOS ?> módulos.</div></div>
    </div>

    <div class="form-actions">
        <a href="rodizios.php" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary" id="btnGerar" disabled><i class="bi bi-check2"></i> Gerar ciclo</button>
    </div>
</form>

<script>
(function () {
    var anteriores = <?= json_encode($anterior['janelas'] ?? [], JSON_HEX_TAG) ?>;
    var alunos = <?= (int) $alunosAtivos ?>;
    var MIN = <?= RODIZIO_MIN_MODULOS ?>, MAX = <?= RODIZIO_MAX_MODULOS ?>;
    var box = document.getElementById('janelas');

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function selecionados() { return Array.from(document.querySelectorAll('.modulo-check:checked')); }
    function valores() {
        return Array.from(box.querySelectorAll('.janela')).map(function (row) {
            return { inicio: row.querySelector('[name="inicio[]"]').value, fim: row.querySelector('[name="fim[]"]').value };
        });
    }
    // Data local em Y-m-d (toISOString usaria UTC e poderia voltar um dia).
    function iso(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function br(d) { return d ? d.split('-').reverse().join('/') : '…'; }

    function renderJanelas() {
        var n = selecionados().length, atuais = valores().length ? valores() : anteriores;
        anteriores = [];
        if (n === 0) { box.innerHTML = '<p class="text-muted mb-0">Selecione os módulos para definir as janelas.</p>'; return; }
        var html = '';
        for (var i = 0; i < n; i++) {
            var v = atuais[i] || {};
            html += '<div class="janela form-grid mb-2 align-items-end">' +
                '<div><label class="form-label">Janela ' + (i + 1) + ' — início</label><input type="date" name="inicio[]" class="form-control" required value="' + esc(v.inicio || '') + '"></div>' +
                '<div><label class="form-label">Fim</label><input type="date" name="fim[]" class="form-control" required value="' + esc(v.fim || '') + '"></div></div>';
        }
        box.innerHTML = html;
    }

    function renderPrevia() {
        var mods = selecionados().map(function (c) { return c.dataset.nome; }), n = mods.length;
        var ok = n >= MIN && n <= MAX, previa = document.getElementById('previa');
        document.getElementById('btnGerar').disabled = !ok || alunos < n;
        if (!ok) {
            previa.innerHTML = '<div class="card-body text-muted">' + (n > MAX ? 'No máximo ' + MAX + ' módulos.' : 'Selecione ao menos ' + MIN + ' módulos.') + '</div>';
            document.getElementById('resumoPrevia').textContent = '';
            return;
        }
        var js = valores(), head = '<th>Grupo</th>';
        js.forEach(function (j, i) { head += '<th>Janela ' + (i + 1) + '<br><span class="fw-normal">' + br(j.inicio) + ' – ' + br(j.fim) + '</span></th>'; });
        var body = '';
        for (var g = 0; g < n; g++) {
            body += '<tr><td class="fw-semibold">Grupo ' + String.fromCharCode(65 + g) + '</td>';
            for (var j = 0; j < n; j++) body += '<td>' + esc(mods[(g + j) % n]) + '</td>';
            body += '</tr>';
        }
        previa.innerHTML = '<table class="table"><thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody></table>';
        var sub = Math.max(1, parseInt(document.getElementById('subgrupos').value, 10) || 1);
        document.getElementById('resumoPrevia').textContent = alunos < n
            ? 'Alunos insuficientes: ' + alunos + ' para ' + n + ' grupos'
            : '≈ ' + Math.ceil(alunos / n) + ' alunos por grupo · ≈ ' + Math.ceil(alunos / n / sub) + ' por subgrupo';
    }

    document.querySelectorAll('.modulo-check').forEach(function (c) { c.addEventListener('change', function () { renderJanelas(); renderPrevia(); }); });
    box.addEventListener('change', renderPrevia);
    document.getElementById('subgrupos').addEventListener('input', renderPrevia);

    document.getElementById('btnAutoDatas').addEventListener('click', function () {
        var inicio = document.getElementById('autoInicio').value, semanas = parseInt(document.getElementById('autoSemanas').value, 10);
        if (!inicio || !semanas) { InterMed.alerta({ icon: 'info', text: 'Informe o início do ciclo e as semanas por janela.' }); return; }
        var d = new Date(inicio + 'T00:00:00');
        box.querySelectorAll('.janela').forEach(function (row) {
            var fim = new Date(d); fim.setDate(fim.getDate() + semanas * 7 - 1);
            row.querySelector('[name="inicio[]"]').value = iso(d);
            row.querySelector('[name="fim[]"]').value = iso(fim);
            d = new Date(fim); d.setDate(d.getDate() + 1);
        });
        renderPrevia();
    });

    renderJanelas();
    renderPrevia();
})();
</script>
<?php endif; ?>
<?php layout_fim(); ?>
