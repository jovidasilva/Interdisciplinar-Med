<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/rodizios.php';

final class RodiziosTest extends TestCase
{
    public function testEscalaEhQuadradoLatino(): void
    {
        foreach ([2, 3, 4, 5] as $n) {
            $escala = escala_rotacao($n);
            foreach ($escala as $linha) {
                // Em cada janela, cada módulo recebe exatamente um grupo.
                $this->assertSame(range(0, $n - 1), $this->ordenado($linha));
            }
            for ($g = 0; $g < $n; $g++) {
                // Cada grupo passa por todos os módulos uma única vez.
                $this->assertSame(range(0, $n - 1), $this->ordenado(array_column($escala, $g)));
            }
        }
    }

    public function testDistribuicaoEquilibradaSemPerderAlunos(): void
    {
        $alunos = range(1, 23);
        $grupos = distribuir_em_grupos($alunos, 3, 3, false);

        $todos = [];
        $tamanhosGrupos = [];
        foreach ($grupos as $subgrupos) {
            $tamanhosSub = array_map('count', $subgrupos);
            $this->assertLessThanOrEqual(1, max($tamanhosSub) - min($tamanhosSub));
            $tamanhosGrupos[] = array_sum($tamanhosSub);
            foreach ($subgrupos as $ids) {
                $todos = array_merge($todos, $ids);
            }
        }
        $this->assertLessThanOrEqual(1, max($tamanhosGrupos) - min($tamanhosGrupos));
        sort($todos);
        $this->assertSame($alunos, $todos, 'Cada aluno aparece em exatamente um subgrupo.');
    }

    public function testValidarJanelas(): void
    {
        $this->assertSame([], validar_janelas([
            ['inicio' => '2026-02-01', 'fim' => '2026-03-31'],
            ['inicio' => '2026-04-01', 'fim' => '2026-05-31'],
        ]));
        $this->assertCount(1, validar_janelas([['inicio' => '2026-03-01', 'fim' => '2026-02-01']]));
        $this->assertCount(1, validar_janelas([
            ['inicio' => '2026-02-01', 'fim' => '2026-03-31'],
            ['inicio' => '2026-03-31', 'fim' => '2026-05-31'],
        ]), 'Janela que começa no último dia da anterior se sobrepõe.');
        $this->assertCount(1, validar_janelas([['inicio' => '', 'fim' => '2026-02-01']]));
    }

    private function ordenado(array $valores): array
    {
        sort($valores);
        return $valores;
    }
}
