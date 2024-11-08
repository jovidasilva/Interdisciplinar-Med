<?php
require_once '../../../cfg/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;
    if (!$idaluno) {
        echo "<script>alert('Erro: Aluno não encontrado.'); location.href='realizar-avaliacao.php';</script>";
        exit();
    }

    $idpreceptor = isset($_GET['idpreceptor']) ? intval($_GET['idpreceptor']) : null;
    $idmodulo = isset($_POST['modulo']) ? intval($_POST['modulo']) : null;
    if (!$idmodulo) {
        echo "<script>alert('Erro: Módulo não selecionado.'); location.href='realizar-avaliacao.php';</script>";
        exit();
    }

    $total_pontuacao = 0;
    $total_perguntas = 0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'pergunta_') === 0) {
            $total_pontuacao += intval($value);
            $total_perguntas++;
        }
    }

    $media = $total_perguntas > 0 ? $total_pontuacao / $total_perguntas : 0;
    $data_avaliacao = date('Y-m-d H:i:s');

    // Inserir na tabela de avaliacoes
    $query = "INSERT INTO avaliacoes (nota, data_avaliacao, idaluno, idpreceptor, idmodulo) VALUES ( ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dsiii", $media, $data_avaliacao, $idaluno, $idpreceptor, $idmodulo);
    $stmt->execute();
    $idavaliacao = $stmt->insert_id;

    if ($idavaliacao) {
        // Inserir as respostas
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pergunta_') === 0) {
                $idpergunta = intval(str_replace('pergunta_', '', $key));
                $resposta = intval($value);

                if ($idpergunta > 0) {  // Ignora IDs inválidos
                    // Verificar se a pergunta existe na tabela perguntas_avaliacoes
                    $checkQuery = "SELECT COUNT(*) FROM perguntas_avaliacoes WHERE idpergunta = ?";
                    $checkStmt = $conn->prepare($checkQuery);
                    $checkStmt->bind_param("i", $idpergunta);
                    $checkStmt->execute();
                    $checkStmt->bind_result($count);
                    $checkStmt->fetch();
                    $checkStmt->close();

                    if ($count > 0) {  // Se a pergunta existir
                        $stmtResposta = $conn->prepare("INSERT INTO avaliacoes_respostas (idavaliacao, idpergunta, resposta) VALUES (?, ?, ?)");
                        $stmtResposta->bind_param("iii", $idavaliacao, $idpergunta, $resposta);
                        $stmtResposta->execute();
                        $stmtResposta->close();
                    } else {
                        echo "<script>alert('Erro: Pergunta de ID $idpergunta não encontrada. Resposta não registrada.');</script>";
                    }
                }
            }
        }
    }

    // Verificar se a inserção da avaliação foi bem-sucedida
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Avaliação registrada com sucesso!'); location.href='avaliacoes.php';</script>";
    } else {
        echo "<script>alert('Erro ao registrar a avaliação.'); location.href='avaliacoes.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
