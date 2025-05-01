<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body {
            overflow-y: hidden;
        }

        .card {
            overflow-y: auto;
            max-height: 750px;
        }
    </style>
</head>

<body>

    <?php
    function tipoTexto($tipo)
    {
        switch ($tipo) {
            case '0':
                return 'Aluno';
            case '1':
                return 'Preceptor';
            case '2':
                return 'Coordenação';
            case '3':
                return 'Coordenação e Preceptor';
            default:
                return 'Não definido';
        }
    }

    function ativoTexto($ativo)
    {
        switch ($ativo) {
            case '0':
                return 'Inativo';
            case '1':
                return 'Ativo';
            default:
                return 'Não definido';
        }
    }

    $sql = "SELECT idusuario, nome, tipo, registro, ativo FROM usuarios";
    $result = $conn->query($sql);

    if (!$result) {
        die("Erro na consulta SQL: " . $conn->error);
    }

    ?>

    <div class="container mt-3">
        <div class="card">
            <div class="card-body">
                <form id="formUsuarios" method="post" action="">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <select name="novo_tipo" class="form-select" id="novoTipoSelect">
                                <option value="-1">Alterar Tipo Para...</option>
                                <option value="0">Aluno</option>
                                <option value="1">Preceptor</option>
                                <option value="2">Coordenação</option>
                                <option value="3">Coordenação e Preceptor</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success" onclick="alterarTipoSelecionados()">Alterar Tipo dos Selecionados</button>
                            <button type="button" class="btn btn-danger" onclick="excluirSelecionados()">Excluir Selecionados</button>
                            <button type="button" class="btn btn-warning" onclick="alternarAtivoSelecionados()">Alterar Ativo/Inativo</button>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 100px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" onclick="selecionarTodos(this)">
                                        <label class="form-check-label" for="selectAll">Selecionar Todos</label>
                                    </div>
                                </th>
                                <th style="width: 150px;">Nome</th>
                                <th style="width: 100px;">Tipo</th>
                                <th style="width: 100px;">Registro</th>
                                <th style="width: 100px;">Ativo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr id="linha-<?php echo $row['idusuario']; ?>">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="usuarios[]" value="<?php echo $row['idusuario']; ?>" id="user-<?php echo $row['idusuario']; ?>">
                                            <label class="form-check-label" for="user-<?php echo $row['idusuario']; ?>"></label>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td><?php echo tipoTexto($row['tipo']); ?></td>
                                    <td><?php echo isset($row['registro']) ? htmlspecialchars($row['registro']) : 'N/A'; ?></td>
                                    <td><?php echo ativoTexto($row['ativo']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script>

        function selecionarTodos(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function excluirSelecionados() {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');
            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');

            Swal.fire({
                title: 'Você tem certeza?',
                text: "Esta ação não pode ser revertida!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = '?page=excluir-usuarios';
                    form.method = 'POST';
                    form.submit();
                }
            });
        }

        function alterarTipoSelecionados() {
            const novoTipo = document.getElementById('novoTipoSelect').value;
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');

            if (novoTipo === "-1") {
                alert_selecionar_tipo();
                return;
            }

            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');
            form.action = '?page=alterar-tipo';
            form.method = 'POST';
            form.submit();
        }

        function alternarAtivoSelecionados() {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');

            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');
            form.action = '?page=alterar-status';
            form.method = 'POST';
            form.submit();
        }
    </script>

</body>

</html>
