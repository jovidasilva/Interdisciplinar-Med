<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maxFileSize = 5 * 1024 * 1024;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = mime_content_type($file);

        if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
            echo "<div class='alert alert-danger'>Erro: Apenas arquivos CSV são permitidos.</div>";
            echo "<a href=\"?page=listar-modulos\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if ($fileSize > $maxFileSize) {
            echo "<div class='alert alert-danger'>Erro: O arquivo excede o tamanho máximo permitido de 5MB.</div>";
            echo "<a href=\"?page=listar-modulos\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle);

            $success_count = 0;
            $error_count = 0;
            $duplicated_count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $nome_modulo = $data[0];
                $periodo = $data[1];

                if (empty($nome_modulo) || empty($periodo)) {
                    echo "<div class='alert alert-danger'>Dados inválidos na linha do modulo '$nome_modulo'.</div>";
                    $error_count++;
                    continue;
                }

                $stmt = $conn->prepare("SELECT idmodulo FROM modulos WHERE nome_modulo = ?");
                $stmt->bind_param("s", $nome_modulo);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    echo "<div class='alert alert-warning'>O modulo '$nome_modulo' já existe. Pulei esta linha.</div>";
                    $duplicated_count++;
                } else {
                    $stmt = $conn->prepare("INSERT INTO modulos (nome_modulo, periodo) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nome_modulo, $periodo);

                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        echo "<div class='alert alert-danger'>Erro ao inserir o modulo '$nome_modulo'.</div>";
                        $error_count++;
                    }
                }
            }
            fclose($handle);

            echo "<div class='alert alert-success'>$success_count modulos importados com sucesso.</div>";
            if ($duplicated_count > 0) {
                echo "<div class='alert alert-warning'>$duplicated_count modulos já existiam e foram ignorados.</div>";
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
            <h3>Importar Módulos</h3>
            <form action="?page=importar-modulos" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="file" class="form-label">Selecione o arquivo CSV</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Importar</button>
                <a href="?page=listar-modulos" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
