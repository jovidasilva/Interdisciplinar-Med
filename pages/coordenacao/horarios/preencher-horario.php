<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

/** Opções de cada campo dependente, a partir do que já foi escolhido. */
function opcoes_horario(mysqli $conn, int $idunidade, int $iddep, int $idmodulo, int $idsubgrupoAtual = 0): array
{
    $op = ['departamentos' => [], 'modulos' => [], 'subgrupos' => [], 'preceptores' => []];
    if ($idunidade) {
        $op['departamentos'] = db_todos($conn, 'SELECT iddepartamento AS id, nome_departamento AS nome FROM departamentos WHERE idunidade = ? ORDER BY nome_departamento', 'i', [$idunidade]);
        // Se o departamento tem módulos definidos, restringe a eles.
        $op['modulos'] = db_todos($conn, "SELECT m.idmodulo AS id, CONCAT(m.nome_modulo, ' (', m.periodo, 'º)') AS nome
            FROM unidades_modulos um JOIN modulos m ON m.idmodulo = um.idmodulo
            WHERE um.idunidade = ?
              AND (? = 0 OR NOT EXISTS (SELECT 1 FROM modulos_departamentos md WHERE md.iddepartamento = ?)
                   OR EXISTS (SELECT 1 FROM modulos_departamentos md WHERE md.iddepartamento = ? AND md.idmodulo = m.idmodulo))
            ORDER BY m.periodo, m.nome_modulo", 'iiii', [$idunidade, $iddep, $iddep, $iddep]);
    }
    if ($idmodulo) {
        $op['subgrupos'] = db_todos($conn, "SELECT sg.idsubgrupo AS id,
                CONCAT(sg.nome_subgrupo, ' — ', DATE_FORMAT(r.inicio, '%d/%m'), ' a ', DATE_FORMAT(r.fim, '%d/%m/%Y')) AS nome
            FROM rodizios r JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
            WHERE r.idmodulo = ? AND (r.fim >= CURDATE() - INTERVAL 30 DAY OR sg.idsubgrupo = ?)
            ORDER BY r.inicio, sg.nome_subgrupo", 'ii', [$idmodulo, $idsubgrupoAtual]);
        $op['preceptores'] = db_todos($conn, 'SELECT u.idusuario AS id, u.nome FROM preceptores_modulos pm
            JOIN usuarios u ON u.idusuario = pm.idusuario JOIN preceptores_unidades pu ON pu.idusuario = u.idusuario
            WHERE pm.idmodulo = ? AND pu.idunidade = ? AND u.ativo = 1 ORDER BY u.nome', 'ii', [$idmodulo, $idunidade]);
    }
    return $op;
}

if (isset($_GET['opcoes'])) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(opcoes_horario($conn, (int) ($_GET['idunidade'] ?? 0), (int) ($_GET['iddepartamento'] ?? 0), (int) ($_GET['idmodulo'] ?? 0)));
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
$h = ['idhorario' => 0, 'idunidade' => 0, 'iddepartamento' => 0, 'idmodulo' => 0, 'idsubgrupo' => 0, 'idpreceptor' => 0,
    'dia_semana' => 'Segunda', 'hora_inicio' => '07:00', 'hora_fim' => '12:00', 'local' => ''];
if ($id) {
    $h = db_um($conn, 'SELECT * FROM horarios WHERE idhorario = ?', 'i', [$id]) ?? redirecionar('horarios.php', 'Horário não encontrado.', 'error');
}
$h = array_merge($h, $_SESSION['form_horario'] ?? []);
unset($_SESSION['form_horario']);

$unidades = db_todos($conn, 'SELECT idunidade AS id, nome_unidade AS nome FROM unidades ORDER BY nome_unidade');
$op = opcoes_horario($conn, (int) $h['idunidade'], (int) $h['iddepartamento'], (int) $h['idmodulo'], (int) $h['idsubgrupo']);

function options(array $lista, $selecionado, string $vazio): string
{
    $html = '<option value="">' . e($vazio) . '</option>';
    foreach ($lista as $o) {
        $html .= '<option value="' . (int) $o['id'] . '"' . ((int) $o['id'] === (int) $selecionado ? ' selected' : '') . '>' . e($o['nome']) . '</option>';
    }
    return $html;
}

$titulo = $id ? 'Editar horário' : 'Novo horário';
layout_inicio($titulo);
page_header($titulo, 'As opções de cada campo dependem das anteriores: unidade → módulo → subgrupo e preceptor.', botao_voltar('horarios.php'));
?>
<form method="POST" action="acoes-horario.php" class="card" style="max-width: 820px">
    <div class="card-body">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="idhorario" value="<?= (int) $h['idhorario'] ?>">

        <h2 class="section-title">Onde</h2>
        <div class="form-grid mb-4">
            <div>
                <label for="idunidade" class="form-label">Unidade</label>
                <select name="idunidade" id="idunidade" class="form-select dep" required><?= options($unidades, $h['idunidade'], 'Selecione…') ?></select>
            </div>
            <div>
                <label for="iddepartamento" class="form-label">Departamento <span class="text-muted fw-normal">(opcional)</span></label>
                <select name="iddepartamento" id="iddepartamento" class="form-select dep"><?= options($op['departamentos'], $h['iddepartamento'], '—') ?></select>
            </div>
            <div>
                <label for="local" class="form-label">Local <span class="text-muted fw-normal">(sala, ala…)</span></label>
                <input type="text" name="local" id="local" class="form-control" maxlength="90" value="<?= e($h['local']) ?>">
            </div>
        </div>

        <h2 class="section-title">O quê e quem</h2>
        <div class="form-grid mb-4">
            <div>
                <label for="idmodulo" class="form-label">Módulo</label>
                <select name="idmodulo" id="idmodulo" class="form-select dep" required><?= options($op['modulos'], $h['idmodulo'], 'Selecione a unidade…') ?></select>
            </div>
            <div>
                <label for="idsubgrupo" class="form-label">Subgrupo</label>
                <select name="idsubgrupo" id="idsubgrupo" class="form-select" required><?= options($op['subgrupos'], $h['idsubgrupo'], 'Selecione o módulo…') ?></select>
            </div>
            <div>
                <label for="idpreceptor" class="form-label">Preceptor</label>
                <select name="idpreceptor" id="idpreceptor" class="form-select" required><?= options($op['preceptores'], $h['idpreceptor'], 'Selecione o módulo…') ?></select>
            </div>
        </div>

        <h2 class="section-title">Quando</h2>
        <div class="form-grid">
            <div>
                <label for="dia_semana" class="form-label">Dia da semana</label>
                <select name="dia_semana" id="dia_semana" class="form-select" required>
                    <?php foreach (DIAS_SEMANA as $d): ?><option <?= $h['dia_semana'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="hora_inicio" class="form-label">Início</label>
                <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" value="<?= e(hora_curta($h['hora_inicio'])) ?>" required>
            </div>
            <div>
                <label for="hora_fim" class="form-label">Fim</label>
                <input type="time" name="hora_fim" id="hora_fim" class="form-control" value="<?= e(hora_curta($h['hora_fim'])) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="horarios.php" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Salvar horário</button>
        </div>
    </div>
</form>
<script>
(function () {
    var campos = { departamentos: 'iddepartamento', modulos: 'idmodulo', subgrupos: 'idsubgrupo', preceptores: 'idpreceptor' };
    var vazios = { departamentos: '—', modulos: 'Selecione…', subgrupos: 'Selecione…', preceptores: 'Selecione…' };
    function val(id) { return document.getElementById(id).value || 0; }
    function preencher(sel, lista, vazio) {
        var atual = sel.value;
        sel.innerHTML = '';
        sel.add(new Option(lista.length || vazio === '—' ? vazio : 'Nenhuma opção disponível', ''));
        lista.forEach(function (o) { sel.add(new Option(o.nome, o.id, false, String(o.id) === atual)); });
    }
    function atualizar(ev) {
        var p = new URLSearchParams({ opcoes: 1, idunidade: val('idunidade'), iddepartamento: val('iddepartamento'), idmodulo: val('idmodulo') });
        fetch('preencher-horario.php?' + p, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (op) {
                Object.keys(campos).forEach(function (k) {
                    if (ev && ev.target.id === campos[k]) return; // não reescreve o próprio campo alterado
                    preencher(document.getElementById(campos[k]), op[k], vazios[k]);
                });
            });
    }
    document.querySelectorAll('select.dep').forEach(function (s) { s.addEventListener('change', atualizar); });
})();
</script>
<?php layout_fim(); ?>
