<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/csv.php';
require_once __DIR__ . '/../../../includes/validacao.php';
exigir_acesso(PAPEIS_COORDENACAO);

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    [$linhas, $erro] = ler_csv_upload('file');
    if ($erro) {
        redirecionar('importar-usuarios.php', $erro, 'error');
    }

    $resultado = ['importados' => 0, 'duplicados' => [], 'invalidos' => []];
    foreach ($linhas as $n => $d) {
        [$nome, $email, $telefone, $login, $senha, $tipo, $registro, $ativo, $periodo] = array_pad($d, 9, '');
        $tipo = (int) $tipo;
        $periodo = $periodo === '' || (int) $periodo === 0 ? null : (int) $periodo;
        $linhaNum = $n + 2;

        $problema = match (true) {
            $nome === '' || $login === '' || $senha === '' => 'nome, login e senha são obrigatórios',
            !validar_email($email) => 'e-mail inválido',
            !in_array($tipo, [TIPO_ALUNO, TIPO_PRECEPTOR], true) => 'tipo deve ser 0 (aluno) ou 1 (preceptor)',
            !in_array($ativo, ['0', '1'], true) => 'ativo deve ser 0 ou 1',
            $tipo === TIPO_ALUNO && !in_array($periodo, PERIODOS, true) => 'aluno precisa de período (' . implode(', ', PERIODOS) . ')',
            default => null,
        };
        if ($problema) {
            $resultado['invalidos'][] = "Linha {$linhaNum} ({$login}): {$problema}";
            continue;
        }
        if (db_um($conn, 'SELECT 1 FROM usuarios WHERE login = ?', 's', [$login])) {
            $resultado['duplicados'][] = $login;
            continue;
        }
        db_exec($conn, 'INSERT INTO usuarios (nome, email, telefone, login, senha, tipo, registro, ativo, periodo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 'sssssisii',
            [$nome, $email, $telefone, $login, password_hash($senha, PASSWORD_DEFAULT), $tipo, $registro, (int) $ativo, $tipo === TIPO_ALUNO ? $periodo : null]);
        $resultado['importados']++;
    }
}

layout_inicio('Importar usuários');
page_header('Importar usuários', 'Cadastre alunos e preceptores em lote a partir de uma planilha CSV.', botao_voltar('usuarios.php'));
?>
<?php if ($resultado): ?>
    <div class="stats">
        <?= stat_tile('Importados', (string) $resultado['importados'], 'bi-check2-circle') ?>
        <?= stat_tile('Logins já existentes', (string) count($resultado['duplicados']), 'bi-files') ?>
        <?= stat_tile('Linhas inválidas', (string) count($resultado['invalidos']), 'bi-exclamation-triangle') ?>
    </div>
    <?php if ($resultado['invalidos'] || $resultado['duplicados']): ?>
        <div class="card mb-4"><div class="card-body small">
            <?php foreach ($resultado['invalidos'] as $msg): ?><div class="text-danger"><?= e($msg) ?></div><?php endforeach; ?>
            <?php if ($resultado['duplicados']): ?><div class="text-muted mt-2">Ignorados (login já existe): <?= e(implode(', ', $resultado['duplicados'])) ?></div><?php endif; ?>
        </div></div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="card-body">
                <?= csrf_field() ?>
                <label for="file" class="form-label">Arquivo CSV</label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv,text/csv" required>
                <div class="form-text">Separador vírgula ou ponto e vírgula; até 5 MB. A primeira linha (cabeçalho) é ignorada.</div>
                <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-upload"></i> Importar</button></div>
            </div>
        </form>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h2>Formato esperado</h2></div>
            <div class="card-body small">
                <code class="d-block mb-3">nome,email,telefone,login,senha,tipo,registro,ativo,periodo</code>
                <ul class="mb-0 ps-3">
                    <li><strong>tipo</strong>: 0 = aluno, 1 = preceptor</li>
                    <li><strong>registro</strong>: RA (aluno) ou CRM (preceptor)</li>
                    <li><strong>ativo</strong>: 1 = sim, 0 = não</li>
                    <li><strong>periodo</strong>: obrigatório para aluno (<?= implode(', ', PERIODOS) ?>); vazio ou 0 para preceptor</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php layout_fim(); ?>
