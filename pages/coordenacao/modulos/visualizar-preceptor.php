<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h3>Lista de Preceptores Disponíveis para o Módulo</h3>
            <?php
            $idmodulo = $_REQUEST['idmodulo'];

            // Consulta para obter preceptores não associados ao módulo
            $sql = "SELECT u.* FROM usuarios u 
            LEFT JOIN preceptores_modulos pm ON u.idusuario = pm.idusuario 
            WHERE u.tipo = 1 AND (pm.idmodulo IS NULL OR pm.idmodulo != '$idmodulo')";
            $res = $conn->query($sql);

            if (!$res) {
                die("Erro na consulta: " . $conn->error);
            }

            // Consulta para obter preceptores associados ao módulo
            $sqlAssociados = "SELECT u.* FROM usuarios u 
            LEFT JOIN preceptores_modulos pm ON u.idusuario = pm.idusuario 
            WHERE u.tipo = 1 AND pm.idmodulo = '$idmodulo'";
            $resAssociados = $conn->query($sqlAssociados);

            // Estrutura flexível para manter as colunas lado a lado
            echo "<div class='row d-flex'>";

            // Preceptores Associados
            echo "<div class='col-md-6'>";
            echo "<h4>Preceptores Associados:</h4>";
            if ($resAssociados && $resAssociados->num_rows > 0) {
                echo "<form action='?page=acoes-preceptor' method='post'>";
                echo "<input type='hidden' name='acao' value='desassociar'>";
                echo "<input type='hidden' name='idmodulo' value='" . $idmodulo . "'>";
                while ($row = $resAssociados->fetch_object()) {
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='checkbox' name='preceptores[]' value='" . $row->idusuario . "' id='preceptor_" . $row->idusuario . "'>";
                    echo "<label class='form-check-label' for='preceptor_" . $row->idusuario . "'>" . htmlspecialchars($row->nome) . "</label>";
                    echo "</div>";
                }
                echo "<button type='submit' class='btn btn-danger'>Desassociar Preceptores</button>";
                echo "</form>";
            } else {
                echo "<p>Nenhum preceptor associado.</p>";
            }
            echo "</div>";

            // Preceptores Não Associados
            echo "<div class='col-md-6'>";
            echo "<h4>Preceptores Não Associados:</h4>";
            if ($res->num_rows > 0) {
                echo "<form action='?page=acoes-preceptor' method='post'>";
                echo "<input type='hidden' name='acao' value='associar'>";
                echo "<input type='hidden' name='idmodulo' value='" . $idmodulo . "'>";
                while ($row = $res->fetch_object()) {
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='checkbox' name='preceptores[]' value='" . $row->idusuario . "' id='preceptor_" . $row->idusuario . "'>";
                    echo "<label class='form-check-label' for='preceptor_" . $row->idusuario . "'>" . htmlspecialchars($row->nome) . "</label>";
                    echo "</div>";
                }
                echo "<button type='submit' class='btn btn-primary'>Associar Preceptores</button>";
                echo "</form>";
            } else {
                echo "<p>Nenhum preceptor disponível.</p>";
            }
            echo "</div>";

            echo "</div>"; // Fecha a div.row
            ?>
        </div>
    </div>
</div>