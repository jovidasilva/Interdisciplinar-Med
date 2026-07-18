<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

// validar_email() foi extraída de includes/alterar-dados.php para
// includes/validacao.php (veja o comentário no topo daquele arquivo).
// alterar-dados.php tem lógica de topo (session_start(), include de
// cfg/config.php com conexão real ao MySQL, leitura de
// $_SERVER['REQUEST_METHOD']) que não pode ser executada de forma segura em
// um processo de teste isolado sem banco de dados. A função de validação em
// si é pura (sem efeitos colaterais), então mover apenas ela para um arquivo
// próprio permite testá-la sem tocar nesse restante.
require_once __DIR__ . '/../includes/validacao.php';

final class ValidarEmailTest extends TestCase
{
    /**
     * @dataProvider emailsValidosProvider
     */
    public function testAceitaEmailsValidos(string $email): void
    {
        $this->assertTrue((bool) validar_email($email));
    }

    public static function emailsValidosProvider(): array
    {
        return [
            'email simples' => ['usuario@dominio.com'],
            'email com subdominio' => ['usuario@sub.dominio.com.br'],
            'email com ponto no nome local' => ['nome.sobrenome@dominio.com'],
            'email com sinal de mais' => ['usuario+tag@dominio.org'],
        ];
    }

    /**
     * @dataProvider emailsInvalidosProvider
     */
    public function testRejeitaEmailsInvalidos(string $email): void
    {
        $this->assertFalse((bool) validar_email($email));
    }

    public static function emailsInvalidosProvider(): array
    {
        return [
            'sem arroba' => ['usuario.dominio.com'],
            'sem dominio' => ['usuario@'],
            'sem parte local' => ['@dominio.com'],
            'string vazia' => [''],
            'dominio sem ponto' => ['usuario@dominio'],
        ];
    }
}
