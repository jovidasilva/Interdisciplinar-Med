<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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

            <form id="rodizioForm" method="POST" action="processar-rodizios.php">
                <input type="hidden" name="periodo" value="" id="hiddenPeriodo">
                <div class="form mt-3">

    <label class="form-label">Rodízio 1</label>
    <div class="row gx-2">
        <div class="col"><input type="date" class="form-control" id="inicio1" name="inicio1"></div>
        <div class="col"><input type="date" class="form-control" id="fim1" name="fim1"></div>
    </div>
    <input type="hidden" id="modulo1" name="modulo1">
</div>
<div class="form mt-3">
    <label class="form-label">Rodízio 2</label>
    <div class="row gx-2">
        <div class="col"><input type="date" class="form-control" id="inicio2" name="inicio2"></div>
        <div class="col"><input type="date" class="form-control" id="fim2" name="fim2"></div>
    </div>
    <input type="hidden" id="modulo2" name="modulo2">
</div>
<div class="form mt-3">
    <label class="form-label">Rodízio 3</label>
    <div class="row gx-2">
        <div class="col"><input type="date" class="form-control" id="inicio3" name="inicio3"></div>
        <div class="col"><input type="date" class="form-control" id="fim3" name="fim3"></div>
    </div>
    <input type="hidden" id="modulo3" name="modulo3">
</div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> Os grupos e subgrupos serão criados vazios. Você poderá alocar os alunos manualmente após a criação.
                </div>

                <button type="button" class="btn btn-primary mt-3" onclick="gerarRodizios()">Gerar Rodízios</button>

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