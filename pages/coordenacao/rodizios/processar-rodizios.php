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
?>
<?php
csrf_verify_or_die();

// Quantos alunos, no máximo, cada subgrupo deve ter. O número de subgrupos
// por grupo é sempre 3 (domínio fixo: cada grupo rotaciona por 3 módulos ao
// longo dos 3 rodízios), mas quantos alunos cabem em cada um é distribuído
// dinamicamente por round-robin, então ninguém fica de fora e a diferença
// entre o subgrupo mais cheio e o mais vazio nunca passa de 1 aluno.
const SUBGRUPOS_POR_GRUPO = 3;

function distribuirAlunosEmSubgrupos(array $alunos, int $numSubgrupos): array
{
    shuffle($alunos);
    $subgrupos = array_fill(0, $numSubgrupos, []);
    foreach ($alunos as $i => $idaluno) {
        $subgrupos[$i % $numSubgrupos][] = $idaluno;
    }
    return $subgrupos;
}

if (isset($_POST['periodo']) && isset($_POST['inicio1']) && isset($_POST['fim1']) && isset($_POST['inicio2']) && isset($_POST['fim2']) && isset($_POST['inicio3']) && isset($_POST['fim3'])) {
    $periodo = intval($_POST['periodo']);
    $datas = [
        ['inicio' => $_POST['inicio1'], 'fim' => $_POST['fim1']],
        ['inicio' => $_POST['inicio2'], 'fim' => $_POST['fim2']],
        ['inicio' => $_POST['inicio3'], 'fim' => $_POST['fim3']],
    ];

    // Validação básica de datas (defesa em profundidade — o JS já valida,
    // mas o servidor nunca deve confiar só no cliente).
    foreach ($datas as $i => $d) {
        $inicioTs = strtotime($d['inicio']);
        $fimTs = strtotime($d['fim']);
        if ($inicioTs === false || $fimTs === false || $fimTs <= $inicioTs) {
            echo "<script>alert('Datas inválidas no Rodízio " . ($i + 1) . ": a data de término deve ser depois da data de início.'); history.back();</script>";
            exit();
        }
        if ($i > 0) {
            $fimAnteriorTs = strtotime($datas[$i - 1]['fim']);
            if ($inicioTs < $fimAnteriorTs) {
                echo "<script>alert('O Rodízio " . ($i + 1) . " precisa começar depois do término do rodízio anterior.'); history.back();</script>";
                exit();
            }
        }
    }

    // Receber o valor do checkbox "Não preencher grupos com alunos"
    $no_fill_groups = isset($_POST['no_fill_groups']) && $_POST['no_fill_groups'] === 'on';

    // Verificação de rodízios com as mesmas datas
    foreach ($datas as $data) {
        $stmtCheck = $conn->prepare('SELECT COUNT(*) AS total FROM rodizios WHERE inicio = ? AND fim = ?');
        $stmtCheck->bind_param('ss', $data['inicio'], $data['fim']);
        $stmtCheck->execute();
        $rowCheck = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if ($rowCheck['total'] > 0) {
            echo "<script>alert('Já existe um rodízio com essas datas exatas.'); history.back();</script>";
            exit();
        }
    }

    // Módulos disponíveis para o período
    $modulos = [];
    $stmtModulos = $conn->prepare('SELECT idmodulo FROM modulos WHERE periodo = ? ORDER BY idmodulo');
    $stmtModulos->bind_param('i', $periodo);
    $stmtModulos->execute();
    $resultModulos = $stmtModulos->get_result();
    while ($row = $resultModulos->fetch_assoc()) {
        $modulos[] = (int) $row['idmodulo'];
    }
    $stmtModulos->close();

    if (count($modulos) < 3) {
        echo "<script>alert('Não há módulos suficientes para o período selecionado (mínimo de 3).'); history.back();</script>";
        exit();
    }
    // Usa só os 3 primeiros módulos (por idmodulo) se houver mais de 3
    // disponíveis, para manter a regra de 3 módulos em rotação.
    $modulos = array_slice($modulos, 0, 3);

    // Verificar se todos os módulos têm alunos cadastrados, a menos que o checkbox esteja marcado
    if (!$no_fill_groups) {
        foreach ($modulos as $idmodulo) {
            $stmt = $conn->prepare('SELECT COUNT(idusuario) AS totalAlunos FROM modulos_alunos WHERE idmodulo = ?');
            $stmt->bind_param('i', $idmodulo);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ((int) $row['totalAlunos'] === 0) {
                echo "<script>alert('Todos os módulos selecionados precisam ter ao menos um aluno cadastrado (ou marque \\'Não preencher grupos\\').'); history.back();</script>";
                exit();
            }
        }
    }

    // Verificação de módulos já usados em rodízios com datas que se
    // sobrepõem a alguma das 3 novas janelas (em vez de bloquear o módulo
    // para sempre — ele pode voltar a ser usado em outro semestre/rodízio
    // futuro, desde que as datas não colidam com um uso já existente).
    foreach ($modulos as $idmodulo) {
        foreach ($datas as $data) {
            $stmtOverlap = $conn->prepare(
                'SELECT COUNT(*) AS total FROM rodizios WHERE idmodulo = ? AND inicio <= ? AND fim >= ?'
            );
            $stmtOverlap->bind_param('iss', $idmodulo, $data['fim'], $data['inicio']);
            $stmtOverlap->execute();
            $rowOverlap = $stmtOverlap->get_result()->fetch_assoc();
            $stmtOverlap->close();

            if ((int) $rowOverlap['total'] > 0) {
                echo "<script>alert('Um dos módulos selecionados já está em um rodízio com datas sobrepostas às informadas.'); history.back();</script>";
                exit();
            }
        }
    }

    // A partir daqui, qualquer falha desfaz tudo — nada de grupos/subgrupos/
    // rodízios "pela metade" no banco.
    $conn->begin_transaction();

    try {
        // Criação de grupos e subgrupos. Para cada grupo, os alunos são
        // sorteados UMA ÚNICA VEZ e distribuídos por round-robin entre os
        // subgrupos daquele grupo — garante que todo aluno matriculado no
        // módulo entra em algum subgrupo (nada de perda silenciosa) e que a
        // diferença de tamanho entre subgrupos nunca passa de 1.
        $grupoIds = [];
        $subgruposPorGrupo = []; // idgrupo => [idsubgrupo, idsubgrupo, ...]

        for ($i = 0; $i < 3; $i++) {
            $nomeGrupo = chr(65 + $i); // A, B, C
            $stmtGrupo = $conn->prepare('INSERT INTO grupos (nome_grupo) VALUES (?)');
            $stmtGrupo->bind_param('s', $nomeGrupo);
            $stmtGrupo->execute();
            $idgrupo = $stmtGrupo->insert_id;
            $stmtGrupo->close();
            $grupoIds[] = $idgrupo;

            $alunosDoGrupo = [];
            if (!$no_fill_groups) {
                $stmt = $conn->prepare('SELECT idusuario FROM modulos_alunos WHERE idmodulo = ?');
                $stmt->bind_param('i', $modulos[$i]);
                $stmt->execute();
                $resultAlunos = $stmt->get_result();
                while ($row = $resultAlunos->fetch_assoc()) {
                    $alunosDoGrupo[] = (int) $row['idusuario'];
                }
                $stmt->close();
            }

            $distribuicao = distribuirAlunosEmSubgrupos($alunosDoGrupo, SUBGRUPOS_POR_GRUPO);

            $subgruposPorGrupo[$idgrupo] = [];
            for ($j = 0; $j < SUBGRUPOS_POR_GRUPO; $j++) {
                $nomeSubgrupo = $nomeGrupo . ($j + 1);
                $stmtSubgrupo = $conn->prepare('INSERT INTO subgrupos (idgrupo, nome_subgrupo) VALUES (?, ?)');
                $stmtSubgrupo->bind_param('is', $idgrupo, $nomeSubgrupo);
                $stmtSubgrupo->execute();
                $idsubgrupo = $stmtSubgrupo->insert_id;
                $stmtSubgrupo->close();
                $subgruposPorGrupo[$idgrupo][] = $idsubgrupo;

                foreach ($distribuicao[$j] as $idaluno) {
                    $stmtAlunoSubgrupo = $conn->prepare('INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)');
                    $stmtAlunoSubgrupo->bind_param('ii', $idaluno, $idsubgrupo);
                    $stmtAlunoSubgrupo->execute();
                    $stmtAlunoSubgrupo->close();
                }
            }
        }

        // Criação dos rodízios: cada um dos 3 grupos passa pelos 3 módulos
        // em rotação cíclica ao longo das 3 janelas de data.
        $rodizioModulos = [
            [0, 1, 2],
            [1, 2, 0],
            [2, 0, 1],
        ];

        foreach ($datas as $index => $data) {
            foreach ($grupoIds as $g => $idgrupo) {
                $modulo = $modulos[$rodizioModulos[$index][$g]];

                $stmtRodizio = $conn->prepare('INSERT INTO rodizios (periodo, inicio, fim, idmodulo) VALUES (?, ?, ?, ?)');
                $stmtRodizio->bind_param('issi', $periodo, $data['inicio'], $data['fim'], $modulo);
                $stmtRodizio->execute();
                $idrodizio = $stmtRodizio->insert_id;
                $stmtRodizio->close();

                foreach ($subgruposPorGrupo[$idgrupo] as $idsubgrupo) {
                    $stmtRodizioSubgrupo = $conn->prepare('INSERT INTO rodizios_subgrupos (idrodizio, idsubgrupo) VALUES (?, ?)');
                    $stmtRodizioSubgrupo->bind_param('ii', $idrodizio, $idsubgrupo);
                    $stmtRodizioSubgrupo->execute();
                    $stmtRodizioSubgrupo->close();
                }
            }
        }

        $conn->commit();
        echo "<script>alert('Rodízios e grupos criados com sucesso!'); location.href='rodizios.php';</script>";
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('Falha ao gerar rodízios: ' . $e->getMessage());
        echo "<script>alert('Erro ao gerar os rodízios. Nenhum dado foi salvo. Tente novamente.'); history.back();</script>";
    }
} else {
    echo "Erro: Dados insuficientes para criar rodízios.";
}
