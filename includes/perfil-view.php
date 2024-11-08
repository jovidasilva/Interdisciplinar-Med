<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../cfg/config.php');

$stmt = $conn->prepare("SELECT nome, email, telefone, registro, login FROM usuarios WHERE idusuario = ?");
$stmt->bind_param("i", $_SESSION['idusuario']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Usuário não encontrado.";
    exit;
}

$stmt->close();
$conn->close();

$email = $_SESSION['form_data']['email'] ?? $user['email'];
$telefone = $_SESSION['form_data']['telefone'] ?? $user['telefone'];

unset($_SESSION['form_data']);
?>

<div class="container mt-5">
    <h2>Informações do Usuário</h2>

    <form action="alterar-dados.php" method="POST">
        <h3>Dados de contato</h3>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
        </div>
        <div class="mb-3">
            <label for="login" class="form-label">Login</label>
            <div class="input-group">
                <input type="text" class="form-control" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" readonly>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#alterarLoginModal">Alterar</button>
            </div>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <div class="input-group">
                <input type="password" class="form-control" id="senha" name="senha" value="********" readonly>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#alterarSenhaModal">Alterar</button>
            </div>
        </div>
        <input type="hidden" name="idusuario" value="<?php echo $_SESSION['idusuario']; ?>">
        <input type="hidden" name="action" value="alterar_dados_contato">
        <button type="submit" class="btn btn-success">Salvar Alterações</button>
        <a onclick="history.back()" class="btn btn-secondary">Voltar</a>
    </form>

    <!-- Modal para alterar login -->
    <div class="modal fade" id="alterarLoginModal" tabindex="-1" aria-labelledby="alterarLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alterarLoginModalLabel">Alterar Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="alterar-dados.php">
                        <div class="form-group">
                            <label for="login_antigo">Login Atual</label>
                            <input type="text" class="form-control" id="login_antigo" name="login_antigo" required>
                        </div>
                        <div class="form-group">
                            <label for="login_novo">Login Novo</label>
                            <input type="text" class="form-control" id="login_novo" name="login_novo" required>
                        </div>
                        <input type="hidden" name="idusuario" value="<?php echo $_SESSION['idusuario']; ?>">
                        <input type="hidden" name="action" value="alterar_login">
                        <button type="submit" class="btn btn-primary mt-3">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para alterar senha -->
    <div class="modal fade" id="alterarSenhaModal" tabindex="-1" aria-labelledby="alterarSenhaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alterarSenhaModalLabel">Alterar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="alterar-dados.php">
                        <div class="form-group">
                            <label for="senha_antiga">Senha Atual</label>
                            <input type="password" class="form-control" id="senha_antiga" name="senha_antiga" required>
                        </div>
                        <div class="form-group">
                            <label for="senha_nova">Senha Nova</label>
                            <input type="password" class="form-control" id="senha_nova" name="senha_nova" required>
                        </div>
                        <input type="hidden" name="idusuario" value="<?php echo $_SESSION['idusuario']; ?>">
                        <input type="hidden" name="action" value="alterar_senha">
                        <button type="submit" class="btn btn-primary mt-3">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>