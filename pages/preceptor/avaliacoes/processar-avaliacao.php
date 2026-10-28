<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/avaliacao-comum.php';
exigir_acesso(PAPEIS_PRECEPTOR);
exigir_post();

// Autoria sempre da sessão, nunca do formulário.
$idpreceptor = usuario_id();
$idaluno = (int) ($_POST['idaluno'] ?? 0);
$idmodulo = (int) ($_POST['idmodulo'] ?? 0);
$voltar = 'realizar-avaliacao.php?idaluno=' . $idaluno . '&idmodulo=' . $idmodulo;

$contexto = contexto_avaliacao($conn, $idpreceptor, $idaluno, $idmodulo);
if (!$contexto) {
    redirecionar('avaliacoes.php', 'Este aluno não está matriculado em um módulo seu.', 'error');
}
if ($contexto['idavaliacao']) {
    redirecionar('avaliacoes.php', 'Este aluno já foi avaliado neste módulo.', 'info');
}

// Toda pergunta ativa precisa de uma resposta válida da escala.
$perguntas = array_map('intval', array_column(db_todos($conn, 'SELECT idpergunta FROM perguntas_avaliacoes WHERE ativo = 1'), 'idpergunta'));
$enviadas = (array) ($_POST['respostas'] ?? []);
$respostas = [];
foreach ($perguntas as $idpergunta) {
    $valor = (int) ($enviadas[$idpergunta] ?? 0);
    if (!isset(OPCOES_RESPOSTA[$valor])) {
        redirecionar($voltar, 'Responda todas as perguntas antes de enviar.', 'error');
    }
    $respostas[$idpergunta] = $valor;
}
if (!$respostas) {
    redirecionar('avaliacoes.php', 'Não há perguntas ativas para avaliar.', 'error');
}

$nota = round(array_sum($respostas) / count($respostas), 2);

$conn->begin_transaction();
try {
    db_exec($conn, 'INSERT INTO avaliacoes (idaluno, idpreceptor, idmodulo, data_avaliacao, nota) VALUES (?, ?, ?, CURDATE(), ?)', 'iiid',
        [$idaluno, $idpreceptor, $idmodulo, $nota]);
    $idavaliacao = $conn->insert_id;
    foreach ($respostas as $idpergunta => $valor) {
        db_exec($conn, 'INSERT INTO avaliacoes_respostas (idavaliacao, idpergunta, resposta) VALUES (?, ?, ?)', 'iis', [$idavaliacao, $idpergunta, (string) $valor]);
    }
    $conn->commit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    // 1062 = chave única: avaliação concorrente do mesmo par.
    $msg = $e->getCode() === 1062 ? 'Este aluno já foi avaliado neste módulo.' : 'Não foi possível registrar a avaliação. Tente novamente.';
    error_log('Erro ao registrar avaliação: ' . $e->getMessage());
    redirecionar('avaliacoes.php', $msg, 'error');
}

redirecionar('consultar-avaliacao.php?id=' . $idavaliacao, 'Avaliação registrada. Nota final: ' . number_format($nota, 2, ',', '.') . '.');
