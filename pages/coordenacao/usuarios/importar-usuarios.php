<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maxFileSize = 5 * 1024 * 1024;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = mime_content_type($file);

        if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
            echo "<div class='alert alert-danger'>Erro: Apenas arquivos CSV são permitidos.</div>";
            echo "<a href=\"?page=listar-usuarios\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if ($fileSize > $maxFileSize) {
            echo "<div class='alert alert-danger'>Erro: O arquivo excede o tamanho máximo permitido de 5MB.</div>";
            echo "<a href=\"?page=listar-usuarios\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle);

            $success_count = 0;
            $error_count = 0;
            $duplicated_count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $nome = $data[0];
                $email = $data[1];
                $telefone = $data[2];
                $login = $data[3];
                $senha = password_hash($data[4], PASSWORD_DEFAULT);
                $tipo = $data[5];
                $registro = $data[6];
                $ativo = $data[7];
                $periodo = $data[8];

                if (empty($nome) || empty($email) || empty($telefone) || empty($periodo) || empty($login) || empty($senha) || empty($registro) || empty($ativo) || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($tipo, [0, 1])) {
                    echo "<div class='alert alert-danger'>Dados inválidos na linha com login '$login'.</div>";
                    $error_count++;
                    continue;
                }

                $stmt = $conn->prepare("SELECT idusuario FROM usuarios WHERE login = ?");
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    echo "<div class='alert alert-warning'>O login '$login' já existe. Pulei esta linha.</div>";
                    $duplicated_count++;
                } else {
                    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, login, senha, tipo, registro, ativo, periodo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssisii", $nome, $email, $telefone, $login, $senha, $tipo, $registro, $ativo, $periodo);

                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        echo "<div class='alert alert-danger'>Erro ao inserir o usuário '$login'.</div>";
                        $error_count++;
                    }
                }
            }
            fclose($handle);

            echo "<div class='alert alert-success'>$success_count usuários importados com sucesso.</div>";
            if ($duplicated_count > 0) {
                echo "<div class='alert alert-warning'>$duplicated_count usuários já existiam e foram ignorados.</div>";
            }
            if ($error_count > 0) {
                echo "<div class='alert alert-danger'>$error_count erros durante a importação.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Erro ao abrir o arquivo.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Erro no upload do arquivo.</div>";
    }
}
?>

<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h3>Importar Lista de Usuários</h3>
            <form action="?page=importar-usuarios" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="file" class="form-label">Selecione o arquivo CSV</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Importar</button>
                <a href="?page=listar-usuarios" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
</div>