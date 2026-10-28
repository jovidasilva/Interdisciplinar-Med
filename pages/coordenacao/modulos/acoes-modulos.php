<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/csv.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

$acao = $_POST['acao'] ?? '';
$idmodulo = (int) ($_POST['idmodulo'] ?? 0);
$detalhe = 'visualizar-modulos.php?idmodulo=' . $idmodulo;

if ($acao === 'salvar') {
    $nome = trim((string) ($_POST['nome_modulo'] ?? ''));
    $periodo = (int) ($_POST['periodo'] ?? 0);
    if ($nome === '' || !in_array($periodo, PERIODOS, true)) {
        redirecionar('editar-modulos.php?idmodulo=' . $idmodulo, 'Informe o nome e um período válido.', 'error');
    }
    if (db_um($conn, 'SELECT 1 FROM modulos WHERE nome_modulo = ? AND periodo = ? AND idmodulo <> ?', 'sii', [$nome, $periodo, $idmodulo])) {
        redirecionar('editar-modulos.php?idmodulo=' . $idmodulo, 'Já existe um módulo com esse nome nesse período.', 'error');
    }
    if ($idmodulo) {
        db_exec($conn, 'UPDATE modulos SET nome_modulo = ?, periodo = ? WHERE idmodulo = ?', 'sii', [$nome, $periodo, $idmodulo]);
        redirecionar('modulos.php', 'Módulo atualizado.');
    }
    db_exec($conn, 'INSERT INTO modulos (nome_modulo, periodo) VALUES (?, ?)', 'si', [$nome, $periodo]);
    redirecionar('modulos.php', 'Módulo cadastrado.');
}

if ($acao === 'excluir') {
    try {
        db_exec($conn, 'DELETE FROM modulos WHERE idmodulo = ?', 'i', [$idmodulo]);
        redirecionar('modulos.php', 'Módulo excluído.');
    } catch (mysqli_sql_exception $e) {
        // Rodízios e horários referenciam o módulo sem CASCADE, de propósito.
        redirecionar('modulos.php', 'Este módulo está em rodízios ou horários. Exclua o ciclo de rodízio/horários antes.', 'error');
    }
}

if ($acao === 'matricular' || $acao === 'desmatricular') {
    $ids = ids_do_post('alunos');
    $sql = $acao === 'matricular'
        ? 'INSERT IGNORE INTO modulos_alunos (idmodulo, idusuario) SELECT ?, idusuario FROM usuarios WHERE idusuario = ? AND tipo = 0'
        : 'DELETE FROM modulos_alunos WHERE idmodulo = ? AND idusuario = ?';
    $n = 0;
    foreach ($ids as $id) {
        $n += db_exec($conn, $sql, 'ii', [$idmodulo, $id]);
    }
    redirecionar($detalhe, $acao === 'matricular' ? "{$n} aluno(s) matriculado(s)." : "{$n} aluno(s) removido(s) do módulo.");
}

if ($acao === 'upload') {
    // CSV com RA (preferível, é único) ou nome do aluno na primeira coluna.
    [$linhas, $erro] = ler_csv_upload('file');
    if ($erro) {
        redirecionar($detalhe, $erro, 'error');
    }
    $novos = $ja = 0;
    $naoEncontrados = $ambiguos = [];
    foreach ($linhas as $l) {
        $chave = $l[0] ?? '';
        if ($chave === '') {
            continue;
        }
        $achados = db_todos($conn, 'SELECT idusuario FROM usuarios WHERE tipo = 0 AND (registro = ? OR nome = ?)', 'ss', [$chave, $chave]);
        if (count($achados) === 0) {
            $naoEncontrados[] = $chave;
        } elseif (count($achados) > 1) {
            $ambiguos[] = $chave;
        } elseif (db_exec($conn, 'INSERT IGNORE INTO modulos_alunos (idmodulo, idusuario) VALUES (?, ?)', 'ii', [$idmodulo, $achados[0]['idusuario']])) {
            $novos++;
        } else {
            $ja++;
        }
    }
    $msg = "{$novos} matriculado(s), {$ja} já estavam no módulo.";
    if ($naoEncontrados) {
        $msg .= ' Não encontrados: ' . implode(', ', array_slice($naoEncontrados, 0, 10)) . (count($naoEncontrados) > 10 ? '…' : '') . '.';
    }
    if ($ambiguos) {
        $msg .= ' Ambíguos (use o RA): ' . implode(', ', array_slice($ambiguos, 0, 10)) . '.';
    }
    redirecionar($detalhe, $msg, $naoEncontrados || $ambiguos ? 'warning' : 'success');
}

redirecionar('modulos.php', 'Ação inválida.', 'error');
