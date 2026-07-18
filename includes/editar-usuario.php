<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../cfg/config.php');
require_once __DIR__ . '/csrf.php';

if (empty($_SESSION['login'])) {
    echo "Erro ao atualizar dados: usuário não autenticado.";
    exit();
}

csrf_verify_or_die();

// Recebe os dados do formulário
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$idusuario = isset($_POST['idusuario']) ? intval($_POST['idusuario']) : intval($_SESSION['idusuario']);

// Checagem de autorização (IDOR): só permite alterar os próprios dados,
// a menos que o usuário logado seja da coordenação (tipo 2 ou 3).
if ($idusuario != $_SESSION['idusuario'] && !in_array($_SESSION['tipo'], [2, 3], true)) {
    echo "Erro ao atualizar dados: você não tem permissão para alterar este usuário.";
    exit();
}

// Prepara a query de UPDATE
$stmt = $conn->prepare("UPDATE usuarios SET email = ?, telefone = ? WHERE idusuario = ?");
$stmt->bind_param("ssi", $email, $telefone, $idusuario);

// Executa a query
if ($stmt->execute()) {
    echo "Dados atualizados com sucesso!";
} else {
    echo "Erro ao atualizar dados: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>