<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/rodizios.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

$acao = $_POST['acao'] ?? '';

if ($acao === 'gerar') {
    $periodo = (int) ($_POST['periodo'] ?? 0);
    $modulos = ids_do_post('modulos');
    $subgrupos = (int) ($_POST['subgrupos'] ?? 3);
    $descricao = trim((string) ($_POST['descricao'] ?? '')) ?: "{$periodo}º período — " . date('m/Y');

    $janelas = [];
    foreach ((array) ($_POST['inicio'] ?? []) as $i => $inicio) {
        $janelas[] = ['inicio' => (string) $inicio, 'fim' => (string) ($_POST['fim'][$i] ?? '')];
    }

    if (!in_array($periodo, PERIODOS, true)) {
        redirecionar('gerar-rodizios.php', 'Selecione um período válido.', 'error');
    }

    [$idciclo, $erros] = gerar_ciclo($conn, $periodo, $modulos, $janelas, $subgrupos, mb_substr($descricao, 0, 90));
    if ($erros) {
        // Volta ao formulário preservando o que foi preenchido.
        $_SESSION['form_rodizio'] = compact('modulos', 'subgrupos', 'descricao', 'janelas');
        redirecionar('gerar-rodizios.php?periodo=' . $periodo, implode(' ', $erros), 'error');
    }
    redirecionar('rodizios.php', 'Ciclo de rodízio gerado com sucesso.');
}

if ($acao === 'excluir') {
    $idciclo = (int) ($_POST['idciclo'] ?? 0);
    // CASCADE: grupos -> subgrupos -> alunos_subgrupos/rodizios_subgrupos/horarios; rodizios.
    $afetadas = db_exec($conn, 'DELETE FROM rodizio_ciclos WHERE idciclo = ?', 'i', [$idciclo]);
    redirecionar('rodizios.php', $afetadas ? 'Ciclo excluído.' : 'Ciclo não encontrado.', $afetadas ? 'success' : 'error');
}

redirecionar('rodizios.php', 'Ação inválida.', 'error');
