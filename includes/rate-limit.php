<?php
// Controle simples de tentativas de login (mitigação de força bruta).
// Depende da tabela `login_attempts` (db/migrations/003_login_attempts.sql).

const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_BLOQUEIO_MINUTOS = 15;

/**
 * Retorna o timestamp (string 'Y-m-d H:i:s') até quando o login está
 * bloqueado, ou null se não estiver bloqueado.
 */
function login_esta_bloqueado(mysqli $conn, string $login): ?string
{
    $stmt = $conn->prepare('SELECT bloqueado_ate FROM login_attempts WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->bind_result($bloqueadoAte);
    $existe = $stmt->fetch();
    $stmt->close();

    if ($existe && $bloqueadoAte !== null && strtotime($bloqueadoAte) > time()) {
        return $bloqueadoAte;
    }
    return null;
}

/**
 * Registra uma tentativa de login falha. Bloqueia o login por
 * LOGIN_BLOQUEIO_MINUTOS depois de LOGIN_MAX_TENTATIVAS falhas seguidas.
 * Se o bloqueio anterior já tiver expirado, a contagem reinicia do zero.
 */
function login_registrar_falha(mysqli $conn, string $login): void
{
    $stmt = $conn->prepare('SELECT tentativas, bloqueado_ate FROM login_attempts WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->bind_result($tentativas, $bloqueadoAte);
    $existe = $stmt->fetch();
    $stmt->close();

    if (!$existe || ($bloqueadoAte !== null && strtotime($bloqueadoAte) <= time())) {
        $tentativas = 1;
    } else {
        $tentativas = (int) $tentativas + 1;
    }

    $novoBloqueio = null;
    if ($tentativas >= LOGIN_MAX_TENTATIVAS) {
        $novoBloqueio = date('Y-m-d H:i:s', time() + LOGIN_BLOQUEIO_MINUTOS * 60);
    }

    $stmt = $conn->prepare(
        'INSERT INTO login_attempts (login, tentativas, bloqueado_ate) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE tentativas = VALUES(tentativas), bloqueado_ate = VALUES(bloqueado_ate)'
    );
    $stmt->bind_param('sis', $login, $tentativas, $novoBloqueio);
    $stmt->execute();
    $stmt->close();
}

/**
 * Limpa o histórico de tentativas depois de um login bem-sucedido.
 */
function login_registrar_sucesso(mysqli $conn, string $login): void
{
    $stmt = $conn->prepare('DELETE FROM login_attempts WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->close();
}

/**
 * Quantos minutos faltam até o bloqueio acabar (arredondado para cima).
 */
function login_minutos_restantes(string $bloqueadoAte): int
{
    return (int) max(1, ceil((strtotime($bloqueadoAte) - time()) / 60));
}
