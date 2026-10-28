<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

// Volta para a mesma listagem (filtros/página) — só caminhos internos.
$voltar = (string) ($_POST['voltar'] ?? '');
$voltar = preg_match('#^/[^/]#', $voltar) ? $voltar : 'usuarios.php';

$alteracoes = json_decode((string) ($_POST['alteracoes'] ?? ''), true);
if (!is_array($alteracoes) || !$alteracoes) {
    redirecionar($voltar, 'Nenhuma alteração recebida.', 'warning');
}

$linhas = [];
$ignoradas = 0;
foreach ($alteracoes as $item) {
    $id = (int) ($item['id'] ?? 0);
    $tipo = (int) ($item['tipo'] ?? -99);
    $ativo = (int) ($item['ativo'] ?? -1);
    $periodo = ($item['periodo'] ?? '') === '' ? null : (int) $item['periodo'];

    if ($id <= 0 || !array_key_exists($tipo, TIPOS_USUARIO) || !in_array($ativo, [0, 1], true)
        || ($periodo !== null && !in_array($periodo, PERIODOS, true))) {
        continue;
    }
    // Ninguém tira o próprio acesso de coordenação nem se desativa por aqui.
    if ($id === usuario_id() && (!in_array($tipo, PAPEIS_COORDENACAO, true) || $ativo === 0)) {
        $ignoradas++;
        continue;
    }
    $linhas[] = [$tipo, $ativo, $periodo, $id];
}

if ($linhas) {
    $conn->begin_transaction();
    try {
        foreach ($linhas as $l) {
            db_exec($conn, 'UPDATE usuarios SET tipo = ?, ativo = ?, periodo = ? WHERE idusuario = ?', 'iiii', $l);
        }
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('Erro ao salvar usuários: ' . $e->getMessage());
        redirecionar($voltar, 'Não foi possível salvar as alterações.', 'error');
    }
}

if ($ignoradas) {
    redirecionar($voltar, 'Alterações salvas, exceto a do seu próprio usuário: você não pode remover seu acesso de coordenação nem se desativar.', 'warning');
}
redirecionar($voltar, count($linhas) . ' usuário(s) atualizado(s).');
