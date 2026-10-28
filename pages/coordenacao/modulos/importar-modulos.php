<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/csv.php';
exigir_acesso(PAPEIS_COORDENACAO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    [$linhas, $erro] = ler_csv_upload('file');
    if ($erro) {
        redirecionar('importar-modulos.php', $erro, 'error');
    }
    $novos = $duplicados = 0;
    $invalidos = [];
    foreach ($linhas as $n => $l) {
        $nome = $l[0] ?? '';
        $periodo = (int) ($l[1] ?? 0);
        if ($nome === '' || !in_array($periodo, PERIODOS, true)) {
            $invalidos[] = $n + 2;
            continue;
        }
        if (db_um($conn, 'SELECT 1 FROM modulos WHERE nome_modulo = ? AND periodo = ?', 'si', [$nome, $periodo])) {
            $duplicados++;
            continue;
        }
        db_exec($conn, 'INSERT INTO modulos (nome_modulo, periodo) VALUES (?, ?)', 'si', [$nome, $periodo]);
        $novos++;
    }
    $msg = "{$novos} módulo(s) importado(s), {$duplicados} já existia(m).";
    if ($invalidos) {
        $msg .= ' Linhas inválidas: ' . implode(', ', $invalidos) . '.';
    }
    redirecionar('modulos.php', $msg, $invalidos ? 'warning' : 'success');
}

layout_inicio('Importar módulos');
page_header('Importar módulos', 'Cadastre vários módulos de uma vez.', botao_voltar('modulos.php'));
?>
<div class="row g-4">
    <div class="col-lg-6">
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="card-body">
                <?= csrf_field() ?>
                <label for="file" class="form-label">Arquivo CSV</label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv,text/csv" required>
                <div class="form-text">Separador vírgula ou ponto e vírgula; a primeira linha é ignorada.</div>
                <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-upload"></i> Importar</button></div>
            </div>
        </form>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h2>Formato esperado</h2></div>
            <div class="card-body small">
                <code class="d-block mb-2">nome do modulo,periodo</code>
                <code class="d-block mb-3">Clínica Médica,9</code>
                Períodos válidos: <?= implode(', ', PERIODOS) ?>.
            </div>
        </div>
    </div>
</div>
<?php layout_fim(); ?>
