<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

$acao = $_POST['acao'] ?? '';

if ($acao === 'excluir') {
    db_exec($conn, 'DELETE FROM horarios WHERE idhorario = ?', 'i', [(int) ($_POST['idhorario'] ?? 0)]);
    redirecionar('horarios.php', 'Horário excluído.');
}

if ($acao !== 'salvar') {
    redirecionar('horarios.php', 'Ação inválida.', 'error');
}

$h = [
    'idhorario' => (int) ($_POST['idhorario'] ?? 0),
    'idunidade' => (int) ($_POST['idunidade'] ?? 0),
    'iddepartamento' => (int) ($_POST['iddepartamento'] ?? 0) ?: null,
    'idmodulo' => (int) ($_POST['idmodulo'] ?? 0),
    'idsubgrupo' => (int) ($_POST['idsubgrupo'] ?? 0),
    'idpreceptor' => (int) ($_POST['idpreceptor'] ?? 0),
    'dia_semana' => (string) ($_POST['dia_semana'] ?? ''),
    'hora_inicio' => (string) ($_POST['hora_inicio'] ?? ''),
    'hora_fim' => (string) ($_POST['hora_fim'] ?? ''),
    'local' => trim((string) ($_POST['local'] ?? '')) ?: null,
];
$voltar = 'preencher-horario.php' . ($h['idhorario'] ? '?id=' . $h['idhorario'] : '');

function falhar(string $msg, array $h, string $voltar): never
{
    $_SESSION['form_horario'] = $h;
    redirecionar($voltar, $msg, 'error');
}

$horaValida = fn($t) => (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $t);
if (!in_array($h['dia_semana'], DIAS_SEMANA, true) || !$horaValida($h['hora_inicio']) || !$horaValida($h['hora_fim'])) {
    falhar('Informe dia e horários válidos.', $h, $voltar);
}
if ($h['hora_fim'] <= $h['hora_inicio']) {
    falhar('A hora de fim deve ser depois da hora de início.', $h, $voltar);
}

// Coerência entre as escolhas (o formulário já filtra, mas o servidor confere).
$checagens = [
    ['SELECT 1 FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?', 'ii', [$h['idunidade'], $h['idmodulo']], 'O módulo não é oferecido nessa unidade.'],
    ['SELECT 1 FROM preceptores_modulos pm JOIN preceptores_unidades pu ON pu.idusuario = pm.idusuario WHERE pm.idusuario = ? AND pm.idmodulo = ? AND pu.idunidade = ?', 'iii',
        [$h['idpreceptor'], $h['idmodulo'], $h['idunidade']], 'O preceptor precisa estar nessa unidade e nesse módulo.'],
    ['SELECT 1 FROM rodizios_subgrupos rs JOIN rodizios r ON r.idrodizio = rs.idrodizio WHERE rs.idsubgrupo = ? AND r.idmodulo = ?', 'ii',
        [$h['idsubgrupo'], $h['idmodulo']], 'Esse subgrupo não tem rodízio nesse módulo.'],
];
if ($h['iddepartamento']) {
    $checagens[] = ['SELECT 1 FROM departamentos WHERE iddepartamento = ? AND idunidade = ?', 'ii', [$h['iddepartamento'], $h['idunidade']], 'O departamento não pertence à unidade.'];
}
foreach ($checagens as [$sql, $tipos, $params, $msg]) {
    if (!db_um($conn, $sql, $tipos, $params)) {
        falhar($msg, $h, $voltar);
    }
}

// Conflito de agenda: mesmo preceptor ou mesmo subgrupo com horário sobreposto no mesmo dia.
$conflito = db_um($conn, "SELECT h.hora_inicio, h.hora_fim, h.idpreceptor, sg.nome_subgrupo, m.nome_modulo
    FROM horarios h JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo JOIN modulos m ON m.idmodulo = h.idmodulo
    WHERE h.dia_semana = ? AND h.hora_inicio < ? AND h.hora_fim > ? AND h.idhorario <> ?
      AND (h.idpreceptor = ? OR h.idsubgrupo = ?)
    LIMIT 1", 'sssiii', [$h['dia_semana'], $h['hora_fim'], $h['hora_inicio'], $h['idhorario'], $h['idpreceptor'], $h['idsubgrupo']]);
if ($conflito) {
    $quem = (int) $conflito['idpreceptor'] === $h['idpreceptor'] ? 'O preceptor já tem' : 'O subgrupo ' . $conflito['nome_subgrupo'] . ' já tem';
    falhar(sprintf('%s atividade (%s, %s–%s) nesse horário de %s.', $quem, $conflito['nome_modulo'], hora_curta($conflito['hora_inicio']), hora_curta($conflito['hora_fim']), $h['dia_semana']), $h, $voltar);
}

$valores = [$h['idunidade'], $h['iddepartamento'], $h['idmodulo'], $h['idsubgrupo'], $h['idpreceptor'], $h['dia_semana'], $h['hora_inicio'], $h['hora_fim'], $h['local']];
if ($h['idhorario']) {
    db_exec($conn, 'UPDATE horarios SET idunidade = ?, iddepartamento = ?, idmodulo = ?, idsubgrupo = ?, idpreceptor = ?, dia_semana = ?, hora_inicio = ?, hora_fim = ?, local = ?
        WHERE idhorario = ?', 'iiiiissssi', array_merge($valores, [$h['idhorario']]));
    redirecionar('horarios.php', 'Horário atualizado.');
}
db_exec($conn, 'INSERT INTO horarios (idunidade, iddepartamento, idmodulo, idsubgrupo, idpreceptor, dia_semana, hora_inicio, hora_fim, local)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 'iiiiissss', $valores);
redirecionar('horarios.php', 'Horário cadastrado.');
