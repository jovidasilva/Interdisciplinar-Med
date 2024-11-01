<?php
include('../../../cfg/config.php');

if (isset($_POST['periodo']) && isset($_POST['inicio1']) && isset($_POST['fim1']) && isset($_POST['inicio2']) && isset($_POST['fim2']) && isset($_POST['inicio3']) && isset($_POST['fim3'])) {
    $periodo = $_POST['periodo'];
    $datas = [
        ['inicio' => $_POST['inicio1'], 'fim' => $_POST['fim1']],
        ['inicio' => $_POST['inicio2'], 'fim' => $_POST['fim2']],
        ['inicio' => $_POST['inicio3'], 'fim' => $_POST['fim3']]
    ];

    // Verificar se já existem rodízios com as mesmas datas
    $rodizioExistente = false;
    foreach ($datas as $data) {
        $inicio = $data['inicio'];
        $fim = $data['fim'];
        
        $queryCheck = "
            SELECT COUNT(*) AS total 
            FROM rodizios 
            WHERE (inicio = ? AND fim = ?)
        ";
        
        $stmtCheck = $conn->prepare($queryCheck);
        $stmtCheck->bind_param("ss", $inicio, $fim);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $rowCheck = $resultCheck->fetch_assoc();
        
        if ($rowCheck['total'] > 0) {
            $rodizioExistente = true;
            break;
        }
    }

    if ($rodizioExistente) {
        echo "<script>alert('Não é possível criar rodízios com as mesmas datas.'); history.back();</script>";
        exit();
    }

    $modulos = [];
    $queryModulos = "SELECT idmodulo FROM modulos WHERE periodo = ?";
    $stmtModulos = $conn->prepare($queryModulos);
    $stmtModulos->bind_param("i", $periodo);
    $stmtModulos->execute();
    $result = $stmtModulos->get_result();
    while ($row = $result->fetch_assoc()) {
        $modulos[] = $row['idmodulo'];
    }
    $stmtModulos->close();

    if (count($modulos) < 3) {
        echo "<script>alert('Não há módulos suficientes para o período selecionado.'); history.back();</script>";
        exit();
    }

    // Verificação de alunos em cada módulo
    $alunosPorModulo = [];
    foreach ($modulos as $idmodulo) {
        $query = "SELECT COUNT(idusuario) AS totalAlunos FROM modulo_alunos WHERE idmodulo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idmodulo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $alunosPorModulo[$idmodulo] = $row['totalAlunos'];
        $stmt->close();
    }

    // Verificar se há alunos em todos os módulos
    if (in_array(0, $alunosPorModulo)) {
        echo "<script>alert('Todos os três módulos devem ter alunos cadastrados.'); history.back();</script>";
        exit();
    }

    // Verificar se os módulos já estão alocados em rodízios existentes
    $modulosExistentes = [];
    foreach ($modulos as $modulo) {
        $queryCheckModulos = "
            SELECT idmodulo 
            FROM rodizios 
            WHERE idmodulo = ?
        ";

        $stmtCheckModulos = $conn->prepare($queryCheckModulos);
        $stmtCheckModulos->bind_param("i", $modulo);
        $stmtCheckModulos->execute();
        $resultCheckModulos = $stmtCheckModulos->get_result();
        
        while ($rowCheckModulos = $resultCheckModulos->fetch_assoc()) {
            $modulosExistentes[] = $rowCheckModulos['idmodulo'];
        }
        $stmtCheckModulos->close();
    }

    if (count($modulosExistentes) > 0) {
        echo "<script>alert('Não é possível criar rodízios com módulos que já existem.'); history.back();</script>";
        exit();
    }

    $alunos = [];
    foreach ($modulos as $idmodulo) {
        $query = "SELECT idusuario FROM modulo_alunos WHERE idmodulo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idmodulo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $alunos[] = $row['idusuario'];
        }
        $stmt->close();
    }
    $alunos = array_unique($alunos);
    shuffle($alunos);

    $gruposGrandes = array_chunk($alunos, ceil(count($alunos) / 3));
    $grupoIds = [];

    foreach ($gruposGrandes as $i => $grupoGrande) {
        $nomeGrupo = chr(65 + $i);
        $stmtGrupo = $conn->prepare("INSERT INTO grupos (nome_grupo) VALUES (?)");
        $stmtGrupo->bind_param("s", $nomeGrupo);
        $stmtGrupo->execute();
        $idgrupo = $stmtGrupo->insert_id;
        $grupoIds[] = $idgrupo;
        $stmtGrupo->close();

        $subgrupos = array_chunk($grupoGrande, 4);
        $subgrupoIds = []; 

        foreach ($subgrupos as $j => $subgrupo) {
            $nomeSubgrupo = $nomeGrupo . ($j + 1);
            $stmtSubgrupo = $conn->prepare("INSERT INTO subgrupos (idgrupo, nome_subgrupo) VALUES (?, ?)");
            $stmtSubgrupo->bind_param("is", $idgrupo, $nomeSubgrupo);
            $stmtSubgrupo->execute();
            $idsubgrupo = $stmtSubgrupo->insert_id;
            $subgrupoIds[] = $idsubgrupo;
            $stmtSubgrupo->close();

            foreach ($subgrupo as $idaluno) {
                $stmtAlunoSubgrupo = $conn->prepare("INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)");
                $stmtAlunoSubgrupo->bind_param("ii", $idaluno, $idsubgrupo);
                $stmtAlunoSubgrupo->execute();
                $stmtAlunoSubgrupo->close();
            }
        }

        $gruposGrandes[$i] = $subgrupoIds;
    }

    $rodizioModulos = [
        [0, 1, 2],
        [1, 2, 0], 
        [2, 0, 1] 
    ];

    foreach ($datas as $index => $data) {
        $inicio = $data['inicio'];
        $fim = $data['fim'];

        foreach ($grupoIds as $g => $idgrupo) {
            $modulo = $modulos[$rodizioModulos[$index][$g]]; 

            $stmtRodizio = $conn->prepare("INSERT INTO rodizios (periodo, inicio, fim, idmodulo) VALUES (?, ?, ?, ?)");
            $stmtRodizio->bind_param("issi", $periodo, $inicio, $fim, $modulo);
            $stmtRodizio->execute();
            $idrodizio = $stmtRodizio->insert_id;
            $stmtRodizio->close();

            foreach ($gruposGrandes[$g] as $idsubgrupo) {
                $stmtRodizioSubgrupo = $conn->prepare("INSERT INTO rodizios_subgrupos (idrodizio, idsubgrupo) VALUES (?, ?)");
                $stmtRodizioSubgrupo->bind_param("ii", $idrodizio, $idsubgrupo);
                $stmtRodizioSubgrupo->execute();
                $stmtRodizioSubgrupo->close();
            }
        }
    }

    echo "<script>alert('Rodízios e grupos criados com sucesso!'); location.href='rodizios.php';</script>";
} else {
    echo "Erro: Dados insuficientes para criar rodízios.";
}
$conn->close();
?>
