<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    $alert_showed = false;
    $alert_type = "";

    switch (@$_REQUEST['alert']) {
        case '1':
            $alert_showed = true;
            $alert_type = "alert_alterar();";
            break;
        case '2':
            $alert_showed = true;
            $alert_type = "alert_erro_alterar();";
            break;
        case '3':
            $alert_showed = true;
            $alert_type = "alert_excluir();";
            break;
        case '4':
            $alert_showed = true;
            $alert_type = "alert_erro_excluir();";
            break;
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
                                <th><input type="checkbox" id="selectAll" onclick="selecionarTodos(this)"> Selecionar Todos</th>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Registro</th>
                                <th>Ativo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr id="linha-<?php echo $row['idusuario']; ?>">
                                    <td><input type="checkbox" name="usuarios[]" value="<?php echo $row['idusuario']; ?>"></td>
                                    <td><?php echo htmlspecialchars($row['idusuario']); ?></td>
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
        function alert_alterar() {
            Swal.fire({
                position: "top",
                icon: "success",
                title: "Tipo(s) alterado(s) com sucesso.",
                showConfirmButton: false,
                timer: 1500
            });
        }

        function alert_erro_alterar() {
            Swal.fire({
                icon: "error",
                title: "Erro alterar",
                text: "Não foi possível alterar o tipo.",
                showConfirmButton: true,
            });
        }

        function alert_excluir() {
            Swal.fire({
                position: "top",
                icon: "success",
                title: "Usuário(s) excluído(s) com sucesso.",
                showConfirmButton: false,
                timer: 1500
            });
        }

        function alert_erro_excluir() {
            Swal.fire({
                icon: "error",
                title: "Erro excluir",
                text: "Não foi possível excluir o(s) usuário(s).",
                showConfirmButton: true,
            });
        }

        function alert_selecionar_tipo() {
            Swal.fire({
                icon: "error",
                title: "Selecione um tipo",
                text: "Você deve selecionar algum tipo!",
                showConfirmButton: true,
            });
        }

        function alert_selecionar_usuario() {
            Swal.fire({
                icon: "error",
                title: "Selecione um usuário",
                text: "Nenhum usuário selecionado!",
                showConfirmButton: true,
            });
        }

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

        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($alert_showed): ?>
                <?php echo $alert_type; ?>

                if (typeof window.history.replaceState === 'function') {
                    const url = window.location.href.split("?")[0];
                    window.history.replaceState(null, null, url);
                }
            <?php endif; ?>
        });
    </script>

</body>

</html>
