<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

// includes/csrf.php inicia uma sessão real (session_start()) na primeira vez
// que é incluído, guardado por `session_status() === PHP_SESSION_NONE`. Isso
// funciona normalmente em CLI/SAPI de teste, mas para evitar qualquer
// problema de permissão com o session.save_path padrão do ambiente, forçamos
// um diretório gravável antes dessa primeira inclusão.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', sys_get_temp_dir());
}

require_once __DIR__ . '/../includes/csrf.php';

/**
 * Testa as funções puras de includes/csrf.php.
 *
 * Como csrf_token()/csrf_verify()/csrf_field() operam sobre $_SESSION e
 * $_POST, e uma sessão real só pode ser iniciada uma vez por processo PHP,
 * simulamos "sessões" diferentes simplesmente reatribuindo $_SESSION como um
 * array comum entre os testes (isso é seguro porque, após o session_start()
 * inicial, $_SESSION passa a se comportar como uma superglobal normal).
 */
final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        unset($_POST['csrf_token']);
    }

    public function testCsrfTokenGeneratesNonEmptyToken(): void
    {
        $token = csrf_token();

        $this->assertIsString($token);
        $this->assertNotSame('', $token);
    }

    public function testCsrfTokenIsStableAcrossCallsInSameSession(): void
    {
        $first = csrf_token();
        $second = csrf_token();

        $this->assertSame($first, $second);
    }

    public function testCsrfTokenDiffersBetweenDifferentSessions(): void
    {
        $tokenSessionA = csrf_token();

        // Simula uma nova sessão (outro usuário/aba), sem token ainda gerado.
        $_SESSION = [];
        $tokenSessionB = csrf_token();

        $this->assertNotSame($tokenSessionA, $tokenSessionB);
    }

    public function testCsrfVerifyReturnsTrueWhenPostTokenMatchesSession(): void
    {
        $token = csrf_token();
        $_POST['csrf_token'] = $token;

        $this->assertTrue(csrf_verify());
    }

    public function testCsrfVerifyReturnsFalseWhenPostTokenDoesNotMatch(): void
    {
        csrf_token();
        $_POST['csrf_token'] = 'token-completamente-invalido';

        $this->assertFalse(csrf_verify());
    }

    public function testCsrfVerifyReturnsFalseWhenPostTokenIsAbsent(): void
    {
        csrf_token();
        unset($_POST['csrf_token']);

        $this->assertFalse(csrf_verify());
    }

    public function testCsrfVerifyReturnsFalseWhenSessionTokenIsAbsent(): void
    {
        // $_SESSION sem 'csrf_token' (ex.: sessão nova) mas com um valor
        // qualquer enviado no POST.
        $_POST['csrf_token'] = 'qualquer-coisa';

        $this->assertFalse(csrf_verify());
    }

    public function testCsrfFieldContainsInputNameAndCurrentToken(): void
    {
        $token = csrf_token();
        $field = csrf_field();

        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString($token, $field);
        $this->assertStringContainsString('type="hidden"', $field);
    }
}
