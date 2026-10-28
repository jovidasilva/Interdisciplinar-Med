<?php
// Regras de geração de um ciclo de rodízio.
//
// Um ciclo pega os alunos ativos de um período, divide-os em N grupos
// (N = número de módulos escolhidos) e cada grupo em S subgrupos. Há N
// janelas de datas consecutivas; em cada janela cada grupo está em um módulo
// diferente, e ao fim do ciclo todo grupo passou por todos os módulos
// exatamente uma vez (quadrado latino).
//
// As funções "puras" (sem banco) ficam separadas para serem testadas em
// tests/RodiziosTest.php.

const RODIZIO_MIN_MODULOS = 2;
const RODIZIO_MAX_MODULOS = 6;
const RODIZIO_MAX_SUBGRUPOS = 6;

/**
 * Distribui os alunos em $numGrupos grupos de $subgrupos subgrupos cada.
 * Retorna [grupo][subgrupo] => [ids]. Tanto os grupos quanto os subgrupos
 * de um mesmo grupo diferem em no máximo 1 aluno.
 */
function distribuir_em_grupos(array $alunos, int $numGrupos, int $subgrupos, bool $embaralhar = true): array
{
    if ($embaralhar) {
        shuffle($alunos);
    }
    $grupos = array_fill(0, $numGrupos, array_fill(0, $subgrupos, []));
    foreach (array_values($alunos) as $i => $idaluno) {
        $grupos[$i % $numGrupos][intdiv($i, $numGrupos) % $subgrupos][] = $idaluno;
    }
    return $grupos;
}

/**
 * Escala de rotação: [janela][grupo] => índice do módulo. Cada linha e cada
 * coluna contém todos os módulos uma única vez.
 */
function escala_rotacao(int $n): array
{
    $escala = [];
    for ($janela = 0; $janela < $n; $janela++) {
        for ($grupo = 0; $grupo < $n; $grupo++) {
            $escala[$janela][$grupo] = ($grupo + $janela) % $n;
        }
    }
    return $escala;
}

/** Nome do grupo pela posição: 0 => A, 1 => B ... */
function nome_grupo(int $indice): string
{
    return chr(65 + $indice);
}

/**
 * Valida as janelas [['inicio' => 'Y-m-d', 'fim' => 'Y-m-d'], ...]: datas
 * válidas, fim depois do início e cada janela começando depois do fim da
 * anterior. Retorna a lista de erros (vazia = ok).
 */
function validar_janelas(array $janelas): array
{
    $erros = [];
    foreach (array_values($janelas) as $i => $j) {
        $n = $i + 1;
        $inicio = DateTime::createFromFormat('!Y-m-d', (string) ($j['inicio'] ?? ''));
        $fim = DateTime::createFromFormat('!Y-m-d', (string) ($j['fim'] ?? ''));
        if (!$inicio || !$fim) {
            $erros[] = "Janela {$n}: informe datas de início e fim válidas.";
            continue;
        }
        if ($fim <= $inicio) {
            $erros[] = "Janela {$n}: o fim deve ser depois do início.";
        }
        if ($i > 0 && isset($fimAnterior) && $inicio <= $fimAnterior) {
            $erros[] = "Janela {$n}: deve começar depois do fim da janela " . ($n - 1) . '.';
        }
        $fimAnterior = $fim;
    }
    return $erros;
}

/**
 * Gera o ciclo inteiro numa transação. Retorna [idciclo, erros]; se houver
 * erros nada é gravado.
 */
function gerar_ciclo(mysqli $conn, int $periodo, array $idsModulos, array $janelas, int $subgrupos, string $descricao): array
{
    $idsModulos = array_values(array_unique(array_map('intval', $idsModulos)));
    $n = count($idsModulos);
    $erros = [];

    if ($n < RODIZIO_MIN_MODULOS || $n > RODIZIO_MAX_MODULOS) {
        $erros[] = 'Escolha de ' . RODIZIO_MIN_MODULOS . ' a ' . RODIZIO_MAX_MODULOS . ' módulos.';
    }
    if (count($janelas) !== $n) {
        $erros[] = 'É preciso uma janela de datas para cada módulo escolhido.';
    }
    if ($subgrupos < 1 || $subgrupos > RODIZIO_MAX_SUBGRUPOS) {
        $erros[] = 'Subgrupos por grupo: de 1 a ' . RODIZIO_MAX_SUBGRUPOS . '.';
    }
    $erros = array_merge($erros, validar_janelas($janelas));
    if ($erros) {
        return [null, $erros];
    }

    // Os módulos precisam existir e ser do período escolhido.
    $marcadores = implode(',', array_fill(0, $n, '?'));
    $validos = db_todos($conn, "SELECT idmodulo FROM modulos WHERE periodo = ? AND idmodulo IN ($marcadores)", 'i' . str_repeat('i', $n), array_merge([$periodo], $idsModulos));
    if (count($validos) !== $n) {
        return [null, ['Algum módulo escolhido não pertence ao período selecionado.']];
    }

    $inicioCiclo = $janelas[0]['inicio'];
    $fimCiclo = $janelas[$n - 1]['fim'];
    $conflito = db_um($conn, 'SELECT c.descricao FROM rodizios r JOIN rodizio_ciclos c ON c.idciclo = r.idciclo
        WHERE r.periodo = ? AND r.inicio <= ? AND r.fim >= ? LIMIT 1', 'iss', [$periodo, $fimCiclo, $inicioCiclo]);
    if ($conflito) {
        return [null, ["As datas se sobrepõem ao ciclo \"{$conflito['descricao']}\" do mesmo período."]];
    }

    $alunos = array_map('intval', array_column(
        db_todos($conn, 'SELECT idusuario FROM usuarios WHERE tipo = 0 AND ativo = 1 AND periodo = ?', 'i', [$periodo]),
        'idusuario'
    ));
    if (count($alunos) < $n) {
        return [null, ["O {$periodo}º período tem " . count($alunos) . " aluno(s) ativo(s); são necessários pelo menos {$n} (um por grupo)."]];
    }

    $distribuicao = distribuir_em_grupos($alunos, $n, $subgrupos);
    $escala = escala_rotacao($n);

    $conn->begin_transaction();
    try {
        db_exec($conn, 'INSERT INTO rodizio_ciclos (periodo, descricao) VALUES (?, ?)', 'is', [$periodo, $descricao]);
        $idciclo = $conn->insert_id;

        $subgruposDoGrupo = [];
        foreach ($distribuicao as $g => $subs) {
            db_exec($conn, 'INSERT INTO grupos (idciclo, nome_grupo) VALUES (?, ?)', 'is', [$idciclo, nome_grupo($g)]);
            $idgrupo = $conn->insert_id;
            foreach ($subs as $s => $idsAlunos) {
                db_exec($conn, 'INSERT INTO subgrupos (idgrupo, nome_subgrupo) VALUES (?, ?)', 'is', [$idgrupo, nome_grupo($g) . ($s + 1)]);
                $idsubgrupo = $conn->insert_id;
                $subgruposDoGrupo[$g][] = $idsubgrupo;
                foreach ($idsAlunos as $idaluno) {
                    db_exec($conn, 'INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)', 'ii', [$idaluno, $idsubgrupo]);
                }
            }
        }

        foreach ($escala as $j => $linha) {
            foreach ($linha as $g => $indiceModulo) {
                db_exec($conn, 'INSERT INTO rodizios (idciclo, periodo, inicio, fim, idmodulo) VALUES (?, ?, ?, ?, ?)', 'iissi',
                    [$idciclo, $periodo, $janelas[$j]['inicio'], $janelas[$j]['fim'], $idsModulos[$indiceModulo]]);
                $idrodizio = $conn->insert_id;
                foreach ($subgruposDoGrupo[$g] as $idsubgrupo) {
                    db_exec($conn, 'INSERT INTO rodizios_subgrupos (idrodizio, idsubgrupo) VALUES (?, ?)', 'ii', [$idrodizio, $idsubgrupo]);
                }
            }
        }

        // Todo aluno do ciclo passa por todos os módulos: garante a matrícula
        // (usada pelas avaliações) sem duplicar as que já existem.
        foreach ($idsModulos as $idmodulo) {
            foreach ($alunos as $idaluno) {
                db_exec($conn, 'INSERT IGNORE INTO modulos_alunos (idmodulo, idusuario) VALUES (?, ?)', 'ii', [$idmodulo, $idaluno]);
            }
        }

        $conn->commit();
        return [$idciclo, []];
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('Falha ao gerar ciclo de rodízio: ' . $e->getMessage());
        return [null, ['Erro ao gravar o ciclo. Nada foi salvo; tente novamente.']];
    }
}
