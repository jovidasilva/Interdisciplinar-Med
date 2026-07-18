<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../cfg/config.php';
require_once __DIR__ . '/csrf.php';

if (empty($_SESSION['login'])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

$stmt = $conn->prepare("SELECT nome, email, telefone, login FROM usuarios WHERE idusuario = ?");
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
    <h2>Editar Perfil</h2>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <form action="editar-usuario.php" method="POST">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="login" class="form-label">Login</label>
            <input type="text" class="form-control" id="login" value="<?php echo htmlspecialchars($user['login']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
        </div>
        <input type="hidden" name="idusuario" value="<?php echo $_SESSION['idusuario']; ?>">
        <button type="submit" class="btn btn-success">Salvar Alterações</button>
        <a href="perfil.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
