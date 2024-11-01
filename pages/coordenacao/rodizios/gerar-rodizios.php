<div class="container mt-3">
    <div class="body">
        <div class="card-body">
            <h1>Selecione o Período</h1>
            <select name="periodo" class="form-select" id="selectPeriodo" onchange="loadModulos()">
                <option value="">Selecione o Período</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
            </select>

            <h2>Módulos Disponíveis</h2>
            <div id="modulosContainer">
                <p>Selecione um período primeiro</p>
            </div>

            <form id="rodizioForm" method="POST" action="processar-rodizio.php">
                <input type="hidden" name="periodo" value="" id="hiddenPeriodo">

                <div class="form mt-3">
                    <label>Rodizio 1</label>
                    <input type="date" id="inicio1" name="inicio1">
                    <input type="date" id="fim1" name="fim1">
                    <input type="hidden" id="modulo1" name="modulo1">
                </div>
                <div class="form mt-3">
                    <label>Rodizio 2</label>
                    <input type="date" id="inicio2" name="inicio2">
                    <input type="date" id="fim2" name="fim2">
                    <input type="hidden" id="modulo2" name="modulo2">
                </div>
                <div class="form mt-3">
                    <label>Rodizio 3</label>
                    <input type="date" id="inicio3" name="inicio3">
                    <input type="date" id="fim3" name="fim3">
                    <input type="hidden" id="modulo3" name="modulo3">
                </div>

                <button type="button" class="btn btn-secondary mt-3" onclick="gerarRodizios()">Gerar rodizio</button>

            </form>
            <button onclick="history.back()" class="btn btn-secondary mt-3">Voltar</button>
        </div>
    </div>
</div>

<script>
    function loadModulos() {
        var periodo = document.getElementById('selectPeriodo').value;
        if (periodo !== '') {
            fetch(`modulos-rodizios.php?periodo=${periodo}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modulosContainer').innerHTML = data;
                })
                .catch(error => {
                    console.error('Erro ao carregar módulos:', error);
                    document.getElementById('modulosContainer').innerHTML = "<p>Erro ao carregar módulos</p>";
                });
        } else {
            document.getElementById('modulosContainer').innerHTML = "<p>Selecione um período primeiro</p>";
        }
    } 
        function gerarRodizios() {
            var inicio1 = document.getElementById('inicio1').value;
            var fim1 = document.getElementById('fim1').value;
            var inicio2 = document.getElementById('inicio2').value;
            var fim2 = document.getElementById('fim2').value;
            var inicio3 = document.getElementById('inicio3').value;
            var fim3 = document.getElementById('fim3').value;
            var periodo = document.getElementById('selectPeriodo').value;
            document.getElementById('hiddenPeriodo').value = periodo;

            if (new Date(fim1) <= new Date(inicio1)) {
                alert("A data de término do Rodízio 1 deve ser após a data de início.");
                return false;
            }
            if (new Date(inicio2) <= new Date(fim1) || new Date(fim2) <= new Date(inicio2)) {
                alert("O Rodízio 2 deve começar após o término do Rodízio 1 e a data de término deve ser após a data de início.");
                return false;
            }
            if (new Date(inicio3) <= new Date(fim2) || new Date(fim3) <= new Date(inicio3)) {
                alert("O Rodízio 3 deve começar após o término do Rodízio 2 e a data de término deve ser após a data de início.");
                return false;
            }

            var modulos = document.querySelectorAll('#modulosContainer li[data-idmodulo]');
            if (modulos.length < 3) {
                alert("É necessário ter ao menos 3 módulos disponíveis.");
                return false;
            }

            var modulosArray = Array.from(modulos).map(modulo => modulo.dataset.idmodulo);


            document.getElementById('modulo1').value = modulosArray[0];
            document.getElementById('modulo2').value = modulosArray[1];
            document.getElementById('modulo3').value = modulosArray[2];

            document.getElementById('modulo1').value = modulosArray[1]; 
            document.getElementById('modulo2').value = modulosArray[2];
            document.getElementById('modulo3').value = modulosArray[0]; 

            document.getElementById('modulo1').value = modulosArray[2]; 
            document.getElementById('modulo2').value = modulosArray[0]; 
            document.getElementById('modulo3').value = modulosArray[1]; 

            document.getElementById('rodizioForm').submit();
        }
</script>