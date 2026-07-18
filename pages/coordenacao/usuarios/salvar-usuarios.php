<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
require_once __DIR__ . '/' . str_repeat('../', 3) . 'includes/csrf.php';

/**
 * Recebe, em um único envio, a lista de usuários cujo tipo e/ou status
 * (ativo) foram alterados na tela de listagem (edição inline por linha) e
 * aplica todas as mudanças de uma vez, em uma transação.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alteracoes'])) {
    csrf_verify_or_die();

    $alteracoes = json_decode($_POST['alteracoes'], true);

    if (!is_array($alteracoes) || count($alteracoes) === 0) {
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert=8';</script>";
        exit();
    }

    $tiposValidos = ['-1', '0', '1', '2', '3'];
    $meuId = (int) ($_SESSION['idusuario'] ?? 0);

    $linhas = [];
    $ignoradas = 0;

    foreach ($alteracoes as $item) {
        $id = isset($item['id']) ? intval($item['id']) : 0;
        $tipo = isset($item['tipo']) ? (string) $item['tipo'] : null;
        $ativo = isset($item['ativo']) ? intval($item['ativo']) : null;

        if ($id <= 0 || $tipo === null || !in_array($tipo, $tiposValidos, true) || !in_array($ativo, [0, 1], true)) {
            continue; // linha malformada, ignora silenciosamente
        }

        // Proteção: ninguém pode, por esta tela, tirar o próprio acesso de
        // coordenação nem se autodesativar (evita autobloqueio acidental).
        if ($id === $meuId && (!in_array($tipo, ['2', '3'], true) || $ativo === 0)) {
            $ignoradas++;
            continue;
        }

        $linhas[] = ['id' => $id, 'tipo' => (int) $tipo, 'ativo' => $ativo];
    }

    if (count($linhas) === 0) {
        $alertCode = $ignoradas > 0 ? '9' : '8';
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert={$alertCode}';</script>";
        exit();
    }

    $conn->begin_transaction();
    $sucesso = true;

    try {
        $stmt = $conn->prepare('UPDATE usuarios SET tipo = ?, ativo = ? WHERE idusuario = ?');
        foreach ($linhas as $linha) {
            $stmt->bind_param('iii', $linha['tipo'], $linha['ativo'], $linha['id']);
            if (!$stmt->execute()) {
                $sucesso = false;
                break;
            }
        }
        $stmt->close();

        if ($sucesso) {
            $conn->commit();
        } else {
            $conn->rollback();
        }
    } catch (Throwable $e) {
        $conn->rollback();
        $sucesso = false;
        error_log('Erro ao salvar alterações de usuários: ' . $e->getMessage());
    }

    if ($sucesso) {
        $alertCode = $ignoradas > 0 ? '9' : '7';
    } else {
        $alertCode = '8';
    }

    echo "<script>location.href='usuarios.php?page=listar-usuarios&alert={$alertCode}';</script>";
    exit();
}
