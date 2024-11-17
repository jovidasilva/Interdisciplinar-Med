<?php
include('../cfg/config.php');

// Recebe os dados do formulário
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$idusuario = $_SESSION['idusuario'];

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