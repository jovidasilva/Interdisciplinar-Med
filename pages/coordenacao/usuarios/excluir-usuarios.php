<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);
exigir_post();

// Nunca exclui o próprio usuário.
$ids = array_values(array_diff(ids_do_post('usuarios'), [usuario_id()]));
if (!$ids) {
    redirecionar('usuarios.php', 'Selecione ao menos um usuário (você não pode excluir a si mesmo).', 'warning');
}

try {
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $n = db_exec($conn, "DELETE FROM usuarios WHERE idusuario IN ($marcadores)", str_repeat('i', count($ids)), $ids);
    redirecionar('usuarios.php', "{$n} usuário(s) excluído(s).");
} catch (mysqli_sql_exception $e) {
    error_log('Erro ao excluir usuários: ' . $e->getMessage());
    redirecionar('usuarios.php', 'Não foi possível excluir: há registros vinculados. Desative o usuário em vez de excluí-lo.', 'error');
}
