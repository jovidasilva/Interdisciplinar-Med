// Comportamentos compartilhados da interface (carregado por layout_scripts()).
(function () {
    'use strict';

    var root = document.documentElement;

    function swal(opts) {
        return window.Swal ? Swal.fire(Object.assign({ confirmButtonColor: getComputedStyle(root).getPropertyValue('--bs-primary').trim() }, opts)) : Promise.resolve({ isConfirmed: confirm(opts.text || opts.title) });
    }

    // Mensagens enviadas pelo servidor (flash): sucesso vira toast discreto,
    // erro/aviso vira diálogo para garantir que a pessoa leia.
    (window.FLASH || []).forEach(function (f) {
        if (f.tipo === 'success' && window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: f.msg, showConfirmButton: false, timer: 3500, timerProgressBar: true });
        } else {
            swal({ icon: f.tipo === 'error' ? 'error' : (f.tipo === 'warning' ? 'warning' : 'info'), text: f.msg });
        }
    });

    // <form data-confirm="Mensagem"> pede confirmação antes de enviar.
    document.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!form.matches('[data-confirm]') || form.dataset.confirmed) return;
        ev.preventDefault();
        swal({ icon: 'warning', title: 'Confirmar ação', text: form.dataset.confirm, showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar', confirmButtonColor: '#b42318' })
            .then(function (r) {
                if (r.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.requestSubmit ? form.requestSubmit(ev.submitter) : form.submit();
                }
            });
    });

    // <form data-require-check="nome[]"> exige ao menos um checkbox marcado.
    document.addEventListener('submit', function (ev) {
        var form = ev.target, campo = form.dataset && form.dataset.requireCheck;
        if (!campo || form.querySelector('input[name="' + campo + '"]:checked')) return;
        ev.preventDefault();
        ev.stopImmediatePropagation();
        swal({ icon: 'info', text: 'Selecione ao menos um item.' });
    }, true);

    // <input type="checkbox" data-select-all="classe"> marca/desmarca o grupo.
    document.addEventListener('change', function (ev) {
        var alvo = ev.target.dataset && ev.target.dataset.selectAll;
        if (!alvo) return;
        document.querySelectorAll('.' + alvo).forEach(function (cb) { cb.checked = ev.target.checked; });
    });

    // Filtro de texto em listas: <input data-filter="#idDaLista">.
    document.addEventListener('input', function (ev) {
        var sel = ev.target.dataset && ev.target.dataset.filter;
        if (!sel) return;
        var termo = ev.target.value.trim().toLowerCase();
        document.querySelectorAll(sel + ' [data-filter-item]').forEach(function (el) {
            el.hidden = termo !== '' && el.textContent.toLowerCase().indexOf(termo) === -1;
        });
    });

    // Sidebar no celular.
    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
        b.addEventListener('click', function () { document.body.classList.add('sidebar-open'); });
    });
    document.querySelectorAll('[data-sidebar-close]').forEach(function (b) {
        b.addEventListener('click', function () { document.body.classList.remove('sidebar-open'); });
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') document.body.classList.remove('sidebar-open');
    });

    // Tema claro/escuro.
    document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
        b.addEventListener('click', function () {
            var novo = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', novo);
            try { localStorage.setItem('intermed-tema', novo); } catch (e) {}
        });
    });

    window.InterMed = { alerta: swal };
})();
