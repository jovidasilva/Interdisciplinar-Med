<?php
session_start();
include('../../../cfg/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit();
}

$action = $_POST['action'] ?? '';
$idsubgrupo = isset($_POST['idsubgrupo']) ? intval($_POST['idsubgrupo']) : 0;

if (!$idsubgrupo) {
    echo json_encode(['success' => false, 'message' => 'Subgrupo não informado']);
    exit();
}

try {
    if ($action === 'alocar_multiplos') {
        // Alocar múltiplos alunos
        $idusuarios = isset($_POST['idusuarios']) ? $_POST['idusuarios'] : '';
        if (empty($idusuarios)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum aluno selecionado']);
            exit();
        }
        
        $ids = array_map('intval', explode(',', $idusuarios));
        $sucesso = 0;
        $erros = [];
        
        foreach ($ids as $idusuario) {
            // Verifica se já está alocado
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM alunos_subgrupos WHERE idusuario = ?");
            $checkStmt->bind_param("i", $idusuario);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($count > 0) {
                $erros[] = "Aluno ID {$idusuario} já está em outro subgrupo";
                continue;
            }
            
            // Insere alocação
            $stmt = $conn->prepare("INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)");
            $stmt->bind_param("ii", $idusuario, $idsubgrupo);
            if ($stmt->execute()) {
                $sucesso++;
            }
            $stmt->close();
        }
        
        $mensagem = "{$sucesso} aluno(s) alocado(s) com sucesso";
        if (!empty($erros)) {
            $mensagem .= ". Erros: " . implode(', ', $erros);
        }
        
        echo json_encode(['success' => $sucesso > 0, 'message' => $mensagem]);
        
    } elseif ($action === 'remover_multiplos') {
        // Remover múltiplos alunos
        $idusuarios = isset($_POST['idusuarios']) ? $_POST['idusuarios'] : '';
        if (empty($idusuarios)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum aluno selecionado']);
            exit();
        }
        
        $ids = array_map('intval', explode(',', $idusuarios));
        $sucesso = 0;
        
        foreach ($ids as $idusuario) {
            $stmt = $conn->prepare("DELETE FROM alunos_subgrupos WHERE idusuario = ? AND idsubgrupo = ?");
            $stmt->bind_param("ii", $idusuario, $idsubgrupo);
            if ($stmt->execute()) {
                $sucesso++;
            }
            $stmt->close();
        }
        
        echo json_encode(['success' => $sucesso > 0, 'message' => "{$sucesso} aluno(s) removido(s) com sucesso"]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?>
