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
$mensagem = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['idmodulo'])) {
    csrf_verify_or_die();

    $idmodulo = $_POST['idmodulo'];
    $file = $_FILES['file'];

    if ($file['error'] == 0) {
        $filePath = $file['tmp_name'];
        $handle = fopen($filePath, "r");

        $alunosInscritos = [];
        $alunosNaoInscritos = [];
        $alunosNaoEncontrados = [];
        $alunosAmbiguos = [];

        // Descarta a linha de cabeçalho do CSV
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $nomeAluno = trim($data[0]);


            $stmt = $conn->prepare("SELECT idusuario FROM usuarios WHERE nome = ? AND tipo = 0");
            $stmt->bind_param("s", $nomeAluno);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 1) {
                $alunosAmbiguos[] = $nomeAluno;
                $stmt->close();
                continue;
            }

            $stmt->bind_result($idusuario);

            if ($stmt->fetch()) {
                $stmt->close();

                $stmtCheck = $conn->prepare("SELECT * FROM modulos_alunos WHERE idmodulo = ? AND idusuario = ?");
                $stmtCheck->bind_param("ii", $idmodulo, $idusuario);
                $stmtCheck->execute();
                $checkResult = $stmtCheck->get_result();

                if ($checkResult->num_rows === 0) {

                    $stmtInsert = $conn->prepare("INSERT INTO modulos_alunos (idmodulo, idusuario) VALUES (?, ?)");
                    $stmtInsert->bind_param("ii", $idmodulo, $idusuario);
                    if ($stmtInsert->execute()) {
                        $alunosInscritos[] = $nomeAluno;
                    }
                    $stmtInsert->close();
                } else {

                    $alunosNaoInscritos[] = $nomeAluno;
                }
            } else {
                $stmt->close();
                $alunosNaoEncontrados[] = $nomeAluno;
            }
        }
        fclose($handle);


        if (!empty($alunosInscritos)) {
            $alunosInscritosSeguro = array_map('htmlspecialchars', $alunosInscritos);
            $mensagem .= "Alunos associados com sucesso:<br><ul><li>" . implode("</li><li>", $alunosInscritosSeguro) . "</li></ul>";
            $alertType = 'success'; // Sucesso
        }
        if (!empty($alunosNaoInscritos)) {
            $alunosNaoInscritosSeguro = array_map('htmlspecialchars', $alunosNaoInscritos);
            $mensagem .= "Os seguintes alunos já estão associados:<br><ul><li>" . implode("</li><li>", $alunosNaoInscritosSeguro) . "</li></ul>";
            $alertType = 'warning'; // Alerta de aviso
        }
        if (!empty($alunosAmbiguos)) {
            $alunosAmbiguosSeguro = array_map('htmlspecialchars', $alunosAmbiguos);
            $mensagem .= "Nomes ambíguos (mais de um aluno com esse nome — associe manualmente pela tela de módulos):<br><ul><li>" . implode("</li><li>", $alunosAmbiguosSeguro) . "</li></ul>";
            $alertType = 'warning'; // Alerta de aviso
        }
        if (!empty($alunosNaoEncontrados)) {
            $alunosNaoEncontradosSeguro = array_map('htmlspecialchars', $alunosNaoEncontrados);
            $mensagem .= "Os seguintes nomes não foram encontrados:<br><ul><li>" . implode("</li><li>", $alunosNaoEncontradosSeguro) . "</li></ul>";
            $alertType = 'warning'; // Alerta de aviso
        }
    } else {
        $mensagem = "Erro ao fazer upload do arquivo.";
        $alertType = 'error'; // Erro
    }
} elseif (!isset($_GET['idmodulo'])) {
    $mensagem = "ID do módulo não informado.";
    $alertType = 'error'; // Erro
    exit();
} else {
    $idmodulo = $_GET['idmodulo'];
}

$conn->close();
?>

<div class="container mt-3">
    <div class="card mt-3 mb-3">
        <div class="card-header">
            <h5>Upload de Relação de Alunos</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="idmodulo" value="<?php echo htmlspecialchars($idmodulo); ?>">
                <div class="mb-3">
                    <label for="fileInput" class="form-label">Selecione o arquivo:</label>
                    <input type="file" name="file" id="fileInput" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-secondary">Upload</button>
                <a class="btn btn-secondary" href="?page=visualizar-modulos&idmodulo=<?php echo $idmodulo; ?>">Voltar</a>
            </form>
        </div>
    </div>
</div>


<?php if (!empty($mensagem)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const alertType = <?php echo json_encode($alertType, JSON_HEX_TAG); ?>;
        const mensagem = <?php echo json_encode($mensagem, JSON_HEX_TAG); ?>;

        if (alertType === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                html: mensagem,
                showConfirmButton: true,
            });
        } else if (alertType === 'warning') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                html: mensagem,
                showConfirmButton: true,
            });
        } else if (alertType === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: mensagem,
                showConfirmButton: true,
            });
        }
    </script>
<?php endif; ?>