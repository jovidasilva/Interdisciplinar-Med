<?php
include('../../../cfg/config.php');

function checkLogin() {
    session_start();
    if (empty($_SESSION["login"])) {
        echo "<script>location.href='../../index.php';</script>";
        exit();
    }
}

function getHorarioData($conn, $idHorario) {
    $query = "SELECT h.*, m.nome_modulo, sg.nome_subgrupo, u.nome AS preceptor_nome 
              FROM horarios h
              LEFT JOIN modulos m ON h.idmodulo = m.idmodulo
              LEFT JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
              LEFT JOIN usuarios u ON h.idpreceptor = u.idusuario
              WHERE h.idhorario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idHorario);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function saveOrUpdateHorario($conn, $data) {
    $idHorario = $data['idHorario'] ?? null;
    $idUnidade = $data['idUnidade'];
    $idModulo = $data['idModulo'];
    $subgrupo = $data['subgrupo'];
    $idPreceptor = $data['idPreceptor'];
    $horaInicio = $data['horaInicio'];
    $horaFim = $data['horaFim'];
    $diaSemana = $data['diaSemana'];

    // Verificar se um horário semelhante já existe
    $query = "SELECT idhorario FROM horarios 
              WHERE idunidade = ? AND idmodulo = ? AND dia_semana = ? AND hora_inicio = ? AND hora_fim = ? AND idhorario <> ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisssi", $idUnidade, $idModulo, $diaSemana, $horaInicio, $horaFim, $idHorario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Um horário semelhante já existe.'];
    }

    if ($idHorario) {
        // Atualizar o horário existente
        $sql = "UPDATE horarios 
                SET idunidade = ?, idmodulo = ?, idpreceptor = ?, dia_semana = ?, hora_inicio = ?, hora_fim = ?, idsubgrupo = ? 
                WHERE idhorario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssii", $idUnidade, $idModulo, $idPreceptor, $diaSemana, $horaInicio, $horaFim, $subgrupo, $idHorario);
    } else {
        // Inserir um novo horário
        $sql = "INSERT INTO horarios (idunidade, idmodulo, idpreceptor, dia_semana, hora_inicio, hora_fim, idsubgrupo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssi", $idUnidade, $idModulo, $idPreceptor, $diaSemana, $horaInicio, $horaFim, $subgrupo);
    }

    if ($stmt->execute()) {
        return ['success' => true, 'message' => ' Novo horário inserido com sucesso!'];
    } else {
        return ['success' => false, 'message' => 'Erro ao inserir novo horário: ' . $stmt->error];
    }
}

function getModulos($conn, $idUnidade) {
    $query = "SELECT DISTINCT m.idmodulo, m.nome_modulo 
              FROM modulos m 
              JOIN unidades_modulos um ON m.idmodulo = um.idmodulo 
              WHERE um.idunidade = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idUnidade);
    $stmt->execute();
    $result = $stmt->get_result();

    $modulos = array();
    while ($row = $result->fetch_assoc()) {
        $modulos[] = $row;
    }

    return json_encode($modulos);
}

function getSubgrupos($conn, $idModulo) {
    $query = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo 
              FROM subgrupos sg
              JOIN rodizios_subgrupos rs ON sg.idsubgrupo = rs.idsubgrupo
              JOIN rodizios r ON rs.idrodizio = r.idrodizio
              WHERE r.idmodulo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idModulo);
    $stmt->execute();
    $result = $stmt->get_result();

    $subgrupos = array();
    while ($row = $result->fetch_assoc()) {
        $subgrupos[] = $row;
    }

    return json_encode($subgrupos);
}

function getPreceptores($conn, $idModulo, $idUnidade) {
    $query = "SELECT DISTINCT u.idusuario, u.nome 
              FROM usuarios u 
              JOIN preceptores_modulos pm ON u.idusuario = pm.idusuario 
              JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
              WHERE pm.idmodulo = ? AND pu.idunidade = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $idModulo, $idUnidade);
    $stmt->execute();
    $result = $stmt->get_result();

    $preceptores = array();
    while ($row = $result->fetch_assoc()) {
        $preceptores[] = $row;
    }

    return json_encode($preceptores);
}

function getUnidades($conn) {
    $query = "SELECT idunidade, nome_unidade FROM unidades";
    $result = $conn->query($query);

    $unidades = array();
    while ($row = $result->fetch_assoc()) {
        $unidades[] = $row;
    }

    return $unidades;
}

function getModulosByUnidade($conn, $idUnidade) {
    $query = "SELECT m.idmodulo, m.nome_modulo 
              FROM modulos m 
              JOIN unidades_modulos um ON m.idmodulo = um.idmodulo 
              WHERE um.idunidade = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idUnidade);
    $stmt->execute();
    $result = $stmt->get_result();

    $modulos = array();
    while ($row = $result->fetch_assoc()) {
        $modulos[] = $row;
    }

    return $modulos;
}

function getSubgruposByModulo($conn, $idModulo) {
    $query = "SELECT sg.idsubgrupo, sg.nome_subgrupo 
              FROM subgrupos sg
              JOIN rodizios_subgrupos rs ON sg.idsubgrupo = rs.idsubgrupo
              JOIN rodizios r ON rs.idrodizio = r.idrodizio
              WHERE r.idmodulo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idModulo);
    $stmt->execute();
    $result = $stmt->get_result();

    $subgrupos = array();
    while ($row = $result->fetch_assoc()) {
        $subgrupos[] = $row;
    }

    return $subgrupos;
}

function getPreceptoresByModuloUnidade($conn, $idModulo, $idUnidade) {
    $query = "SELECT DISTINCT u.idusuario, u.nome 
              FROM usuarios u 
              JOIN preceptores_modulos pm ON u.idusuario = pm.idusuario 
              JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
              WHERE pm.idmodulo = ? AND pu.idunidade = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $idModulo, $idUnidade);
    $stmt->execute();
    $result = $stmt->get_result();

    $preceptores = array();
    while ($row = $result->fetch_assoc()) {
        $preceptores[] = $row;
    }

    return $preceptores;
}
?>