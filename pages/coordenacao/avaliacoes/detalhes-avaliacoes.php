<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/../../../includes/avaliacao-detalhe.php';
exigir_acesso(PAPEIS_COORDENACAO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    // Excluir libera o preceptor para avaliar o aluno de novo neste módulo.
    db_exec($conn, 'DELETE FROM avaliacoes WHERE idavaliacao = ?', 'i', [(int) ($_POST['idavaliacao'] ?? 0)]);
    redirecionar('avaliacoes.php', 'Avaliação excluída. O preceptor pode registrá-la novamente.');
}

$id = (int) ($_GET['idavaliacao'] ?? 0);
$avaliacao = carregar_avaliacao($conn, $id) ?? redirecionar('avaliacoes.php', 'Avaliação não encontrada.', 'error');

layout_inicio('Detalhes da avaliação');
page_header('Avaliação de ' . $avaliacao['aluno'], $avaliacao['nome_modulo'] . ' · por ' . $avaliacao['preceptor'],
    botao_voltar('avaliacoes.php')
    . form_acao('detalhes-avaliacoes.php', ['idavaliacao' => $id], '<i class="bi bi-trash"></i> Excluir', 'btn btn-outline-danger',
        'Excluir esta avaliação? O preceptor poderá registrá-la novamente.'));
render_avaliacao($avaliacao);
layout_fim();
