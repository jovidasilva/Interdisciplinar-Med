<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/avaliacao-detalhe.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$avaliacao = carregar_avaliacao($conn, (int) ($_GET['id'] ?? 0), usuario_id());
if (!$avaliacao) {
    redirecionar('avaliacoes.php', 'Avaliação não encontrada.', 'error');
}

layout_inicio('Avaliação');
page_header('Avaliação de ' . $avaliacao['aluno'], $avaliacao['nome_modulo'], botao_voltar('avaliacoes.php?status=realizadas'));
render_avaliacao($avaliacao);
layout_fim();
