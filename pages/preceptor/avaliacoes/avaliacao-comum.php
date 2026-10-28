<?php
// Regra compartilhada pelo formulário e pelo processamento da avaliação:
// o preceptor só avalia aluno ativo matriculado num módulo que é dele.

/** Dados do par (aluno, módulo) para este preceptor, ou null se não puder avaliar. */
function contexto_avaliacao(mysqli $conn, int $idpreceptor, int $idaluno, int $idmodulo): ?array
{
    return db_um($conn, "SELECT u.nome AS aluno, u.registro, m.nome_modulo, a.idavaliacao
        FROM preceptores_modulos pm
        JOIN modulos m ON m.idmodulo = pm.idmodulo
        JOIN modulos_alunos ma ON ma.idmodulo = pm.idmodulo AND ma.idusuario = ?
        JOIN usuarios u ON u.idusuario = ma.idusuario AND u.tipo = 0 AND u.ativo = 1
        LEFT JOIN avaliacoes a ON a.idaluno = u.idusuario AND a.idmodulo = m.idmodulo AND a.idpreceptor = pm.idusuario
        WHERE pm.idusuario = ? AND pm.idmodulo = ?", 'iii', [$idaluno, $idpreceptor, $idmodulo]);
}
