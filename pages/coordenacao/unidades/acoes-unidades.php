<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

$acao = $_POST['acao'] ?? '';
$idunidade = (int) ($_POST['idunidade'] ?? 0);
$aba = $_POST['aba'] ?? 'modulos';
$detalhe = 'visualizar-unidade.php?idunidade=' . $idunidade . '&aba=' . urlencode($aba);

if ($acao === 'salvar') {
    $nome = trim((string) ($_POST['nome_unidade'] ?? ''));
    $endereco = trim((string) ($_POST['endereco_unidade'] ?? ''));
    if ($nome === '') {
        redirecionar('editar-unidades.php?idunidade=' . $idunidade, 'Informe o nome da unidade.', 'error');
    }
    if ($idunidade) {
        db_exec($conn, 'UPDATE unidades SET nome_unidade = ?, endereco_unidade = ? WHERE idunidade = ?', 'ssi', [$nome, $endereco, $idunidade]);
        redirecionar('visualizar-unidade.php?idunidade=' . $idunidade, 'Unidade atualizada.');
    }
    db_exec($conn, 'INSERT INTO unidades (nome_unidade, endereco_unidade) VALUES (?, ?)', 'ss', [$nome, $endereco]);
    redirecionar('visualizar-unidade.php?idunidade=' . $conn->insert_id, 'Unidade cadastrada. Agora associe módulos e preceptores.');
}

if ($acao === 'excluir') {
    try {
        db_exec($conn, 'DELETE FROM unidades WHERE idunidade = ?', 'i', [$idunidade]);
        redirecionar('unidades.php', 'Unidade excluída.');
    } catch (mysqli_sql_exception $e) {
        redirecionar($detalhe, 'Há horários cadastrados nesta unidade. Exclua-os antes de remover a unidade.', 'error');
    }
}

if ($acao === 'associar_modulos' || $acao === 'desassociar_modulos') {
    $sql = $acao === 'associar_modulos'
        ? 'INSERT IGNORE INTO unidades_modulos (idunidade, idmodulo) VALUES (?, ?)'
        : 'DELETE FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?';
    foreach (ids_do_post('modulos') as $idmodulo) {
        db_exec($conn, $sql, 'ii', [$idunidade, $idmodulo]);
    }
    redirecionar($detalhe, $acao === 'associar_modulos' ? 'Módulos associados.' : 'Módulos removidos da unidade.');
}

if ($acao === 'associar_preceptores') {
    // Um preceptor pertence a uma única unidade: associar aqui o move.
    foreach (ids_do_post('preceptores') as $id) {
        db_exec($conn, 'INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES (?, ?) ON DUPLICATE KEY UPDATE idunidade = VALUES(idunidade)', 'ii', [$id, $idunidade]);
    }
    redirecionar($detalhe, 'Preceptores associados à unidade.');
}

if ($acao === 'desassociar_preceptores') {
    foreach (ids_do_post('preceptores') as $id) {
        db_exec($conn, 'DELETE FROM preceptores_unidades WHERE idusuario = ? AND idunidade = ?', 'ii', [$id, $idunidade]);
    }
    redirecionar($detalhe, 'Preceptores removidos da unidade. Os módulos deles foram mantidos.');
}

if ($acao === 'adicionar_departamento') {
    $nome = trim((string) ($_POST['nome_departamento'] ?? ''));
    if ($nome === '') {
        redirecionar($detalhe, 'Informe o nome do departamento.', 'error');
    }
    db_exec($conn, 'INSERT INTO departamentos (nome_departamento, idunidade) VALUES (?, ?)', 'si', [$nome, $idunidade]);
    redirecionar($detalhe, 'Departamento adicionado.');
}

if ($acao === 'excluir_departamento') {
    db_exec($conn, 'DELETE FROM departamentos WHERE iddepartamento = ? AND idunidade = ?', 'ii', [(int) ($_POST['iddepartamento'] ?? 0), $idunidade]);
    redirecionar($detalhe, 'Departamento removido. Horários dele ficaram sem departamento.');
}

if ($acao === 'departamento_modulos') {
    // Substitui a lista de módulos do departamento pela marcada no formulário,
    // aceitando só módulos que a unidade oferece.
    $iddep = (int) ($_POST['iddepartamento'] ?? 0);
    if (!db_um($conn, 'SELECT 1 FROM departamentos WHERE iddepartamento = ? AND idunidade = ?', 'ii', [$iddep, $idunidade])) {
        redirecionar($detalhe, 'Departamento não encontrado.', 'error');
    }
    $conn->begin_transaction();
    db_exec($conn, 'DELETE FROM modulos_departamentos WHERE iddepartamento = ?', 'i', [$iddep]);
    foreach (ids_do_post('modulos') as $idmodulo) {
        db_exec($conn, 'INSERT INTO modulos_departamentos (iddepartamento, idmodulo)
            SELECT ?, idmodulo FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?', 'iii', [$iddep, $idunidade, $idmodulo]);
    }
    $conn->commit();
    redirecionar($detalhe, 'Módulos do departamento atualizados.');
}

redirecionar('unidades.php', 'Ação inválida.', 'error');
