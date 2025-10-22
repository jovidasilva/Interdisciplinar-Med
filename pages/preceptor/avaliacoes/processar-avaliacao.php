<?php
session_start();
require_once '../../../cfg/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $idaluno = isset($_POST['id_aluno']) ? intval($_POST['id_aluno']) : null;
    $idmodulo = isset($_POST['id_modulo']) ? intval($_POST['id_modulo']) : null;
    $idpreceptor = isset($_POST['idpreceptor']) ? intval($_POST['idpreceptor']) : ($_SESSION['idusuario'] ?? null);
    
    if (!$idaluno || !$idmodulo || !$idpreceptor) {
        echo "<script>alert('Erro: Dados insuficientes.'); location.href='avaliacoes.php';</script>";
        exit();
    }

    // Calcula média das respostas (igual ao Java)
    $soma = 0;
    $qtd = 0;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'pergunta_') === 0) {
            $soma += doubleval($value);
            $qtd++;
        }
    }

    $media = $qtd > 0 ? $soma / $qtd : 0;
    
    // Verifica se já existe avaliação para este aluno/preceptor/módulo
    $checkQuery = "SELECT idavaliacao FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ? AND idmodulo = ?";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("iii", $idaluno, $idpreceptor, $idmodulo);
    $stmtCheck->execute();
    $stmtCheck->bind_result($idavaliacaoExistente);
    $avaliacaoExiste = $stmtCheck->fetch();
    $stmtCheck->close();
    
    if ($avaliacaoExiste) {
        // Atualiza avaliação existente
        $query = "UPDATE avaliacoes SET nota = ?, data_avaliacao = NOW() WHERE idavaliacao = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("di", $media, $idavaliacaoExistente);
        $stmt->execute();
        $stmt->close();
        $idavaliacao = $idavaliacaoExistente;
        $mensagem = 'Avaliação atualizada com sucesso!';
    } else {
        // Insere nova avaliação
        $query = "INSERT INTO avaliacoes (idaluno, idpreceptor, idmodulo, nota, data_avaliacao) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiid", $idaluno, $idpreceptor, $idmodulo, $media);
        $stmt->execute();
        $idavaliacao = $stmt->insert_id;
        $stmt->close();
        $mensagem = 'Avaliação realizada com sucesso!';
    }

    // Salvar/atualizar respostas individuais (se houver tabela respostas_avaliacoes)
    if ($idavaliacao && $conn->query("SHOW TABLES LIKE 'respostas_avaliacoes'")->num_rows > 0) {
        // Se foi atualização, remove respostas antigas
        if ($avaliacaoExiste) {
            $stmtDel = $conn->prepare("DELETE FROM respostas_avaliacoes WHERE idavaliacao = ?");
            $stmtDel->bind_param("i", $idavaliacao);
            $stmtDel->execute();
            $stmtDel->close();
        }
        
        // Insere novas respostas
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pergunta_') === 0) {
                $idpergunta = intval(str_replace('pergunta_', '', $key));
                $valorResposta = intval($value);
                
                if ($idpergunta > 0) {
                    $stmtResp = $conn->prepare("INSERT INTO respostas_avaliacoes (idavaliacao, idpergunta, valor_resposta) VALUES (?, ?, ?)");
                    $stmtResp->bind_param("iii", $idavaliacao, $idpergunta, $valorResposta);
                    $stmtResp->execute();
                    $stmtResp->close();
                }
            }
        }
    }

    // Redireciona com mensagem de sucesso
    echo "<script>alert('" . $mensagem . "'); location.href='avaliacoes.php';</script>";
    $conn->close();
    exit();
} else {
    echo "<script>alert('Método inválido.'); location.href='avaliacoes.php';</script>";
    exit();
}
