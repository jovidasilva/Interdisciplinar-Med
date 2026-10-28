<?php
// Layout compartilhado (shell com sidebar + topbar, telas de autenticação)
// e pequenos componentes de interface. Carregado por includes/app.php.

// Cada item: [rótulo, ícone, página do link, ...caminhos que marcam o item ativo].
// O link sempre aponta para um arquivo: as pastas não têm index.php (403).
function menu_do_usuario(): array
{
    $aluno = [
        ['Início', 'bi-house', 'pages/aluno/home.php'],
        ['Horários', 'bi-clock', 'pages/aluno/horarios.php'],
        ['Rodízios', 'bi-arrow-repeat', 'pages/aluno/rodizios.php'],
        ['Notas', 'bi-clipboard-check', 'pages/aluno/notas.php'],
    ];
    $preceptor = [
        ['Início', 'bi-house', 'pages/preceptor/home.php'],
        ['Horários', 'bi-clock', 'pages/preceptor/horarios.php'],
        ['Rodízios', 'bi-arrow-repeat', 'pages/preceptor/rodizios.php'],
        ['Grupos', 'bi-people', 'pages/preceptor/grupos.php'],
        ['Alunos', 'bi-mortarboard', 'pages/preceptor/listar-aluno.php'],
        ['Avaliações', 'bi-clipboard-check', 'pages/preceptor/avaliacoes/avaliacoes.php', 'pages/preceptor/avaliacoes/'],
    ];
    $coordenacao = [
        ['Início', 'bi-house', 'pages/coordenacao/home.php'],
        ['Usuários', 'bi-person-gear', 'pages/coordenacao/usuarios/usuarios.php', 'pages/coordenacao/usuarios/'],
        ['Alunos', 'bi-mortarboard', 'pages/coordenacao/listar-aluno.php'],
        ['Preceptores', 'bi-person-badge', 'pages/coordenacao/listar-preceptor.php', 'pages/coordenacao/associar-preceptor.php'],
        ['Unidades', 'bi-hospital', 'pages/coordenacao/unidades/unidades.php', 'pages/coordenacao/unidades/'],
        ['Módulos', 'bi-grid', 'pages/coordenacao/modulos/modulos.php', 'pages/coordenacao/modulos/'],
        ['Rodízios', 'bi-arrow-repeat', 'pages/coordenacao/rodizios/rodizios.php', 'pages/coordenacao/rodizios/'],
        ['Grupos', 'bi-people', 'pages/coordenacao/grupos/grupos.php', 'pages/coordenacao/grupos/'],
        ['Horários', 'bi-clock', 'pages/coordenacao/horarios/horarios.php', 'pages/coordenacao/horarios/'],
        ['Avaliações', 'bi-clipboard-check', 'pages/coordenacao/avaliacoes/avaliacoes.php', 'pages/coordenacao/avaliacoes/'],
        ['Relatórios', 'bi-bar-chart', 'pages/coordenacao/relatorios.php'],
    ];

    return match (usuario_tipo()) {
        TIPO_ALUNO => ['' => $aluno],
        TIPO_PRECEPTOR => ['' => $preceptor],
        TIPO_COORDENACAO => ['' => $coordenacao],
        TIPO_COORDENACAO_PRECEPTOR => ['' => $coordenacao, 'Preceptoria' => array_slice($preceptor, 1)],
        default => [],
    };
}

function menu_item_ativo(array $item): bool
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    foreach (array_slice($item, 2) as $caminho) {
        if (str_contains($script, '/' . $caminho)) {
            return true;
        }
    }
    return false;
}

function layout_head(string $titulo): void
{
    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> · InterMed</title>
    <script>
        // Aplica o tema antes de pintar a página, para não piscar.
        (function () {
            var t = null;
            try { t = localStorage.getItem('intermed-tema'); } catch (e) {}
            if (!t) t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('css/style.css')) ?>?v=<?= ASSET_VERSION ?>" rel="stylesheet">
</head>
    <?php
}

function layout_scripts(): void
{
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script>window.FLASH = <?= json_encode(flash_consumir(), JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
    <script src="<?= e(url('script/app.js')) ?>?v=<?= ASSET_VERSION ?>"></script>
    <?php
}

/** Abre a página autenticada: sidebar, topbar e área de conteúdo. */
function layout_inicio(string $titulo): void
{
    $menu = menu_do_usuario();
    $nome = $_SESSION['nome'] ?? '';
    $iniciais = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), array_slice(preg_split('/\s+/', trim($nome)) ?: [], 0, 2))));
    layout_head($titulo);
    ?>
<body class="app">
    <a class="visually-hidden-focusable skip-link" href="#conteudo">Pular para o conteúdo</a>
    <aside class="app-sidebar" id="sidebar" aria-label="Menu principal">
        <a class="app-brand" href="<?= e(home_do_usuario()) ?>">
            <span class="app-brand-mark"><i class="bi bi-heart-pulse"></i></span>
            <span class="app-brand-text">InterMed<small>Internato Médico</small></span>
        </a>
        <nav class="app-nav">
            <?php foreach ($menu as $secao => $itens): ?>
                <?php if ($secao !== ''): ?><div class="app-nav-section"><?= e($secao) ?></div><?php endif; ?>
                <?php foreach ($itens as $item): ?>
                    <?php $ativo = menu_item_ativo($item); ?>
                    <a href="<?= e(url($item[2])) ?>" class="app-nav-link<?= $ativo ? ' active' : '' ?>"<?= $ativo ? ' aria-current="page"' : '' ?>>
                        <i class="bi <?= e($item[1]) ?>"></i><span><?= e($item[0]) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="app-backdrop" data-sidebar-close></div>

    <div class="app-main">
        <header class="app-topbar">
            <button class="btn btn-icon d-lg-none" type="button" data-sidebar-toggle aria-label="Abrir menu" aria-controls="sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="app-topbar-title"><?= e($titulo) ?></div>
            <button class="btn btn-icon" type="button" data-theme-toggle aria-label="Alternar tema claro/escuro">
                <i class="bi bi-moon-stars theme-icon-light"></i><i class="bi bi-sun theme-icon-dark"></i>
            </button>
            <div class="dropdown">
                <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar"><?= e($iniciais ?: '?') ?></span>
                    <span class="user-chip-text">
                        <strong><?= e($nome) ?></strong>
                        <small><?= e(TIPOS_USUARIO[usuario_tipo()] ?? '') ?></small>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= e(url('includes/perfil.php')) ?>"><i class="bi bi-person me-2"></i>Meu perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= e(url('cadastro_e_login/logout.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                </ul>
            </div>
        </header>
        <main class="app-content" id="conteudo">
    <?php
}

function layout_fim(): void
{
    ?>
        </main>
    </div>
    <?php layout_scripts(); ?>
</body>
</html>
    <?php
}

/** Abre uma tela pública (login, cadastro, senha). */
function layout_auth_inicio(string $titulo, string $subtitulo = '', bool $largo = false): void
{
    layout_head($titulo);
    ?>
<body class="auth">
    <div class="auth-shell">
        <section class="auth-aside" style="background-image: url('<?= e(url('img/bg.jpg')) ?>')">
            <div class="auth-aside-inner">
                <img src="<?= e(url('img/LogoCeuma.png')) ?>" alt="CEUMA" class="auth-logo">
                <h2>Gestão do internato médico, em um só lugar.</h2>
                <p>Rodízios, horários, grupos e avaliações para alunos, preceptores e coordenação.</p>
            </div>
        </section>
        <section class="auth-panel">
            <div class="auth-card<?= $largo ? ' auth-wide' : '' ?>">
                <div class="auth-brand"><span class="app-brand-mark"><i class="bi bi-heart-pulse"></i></span> InterMed</div>
                <h1 class="auth-title"><?= e($titulo) ?></h1>
                <?php if ($subtitulo !== ''): ?><p class="auth-subtitle"><?= e($subtitulo) ?></p><?php endif; ?>
    <?php
}

function layout_auth_fim(): void
{
    ?>
            </div>
        </section>
    </div>
    <?php layout_scripts(); ?>
</body>
</html>
    <?php
}

/** Cabeçalho de página: título, descrição opcional e ações (HTML já seguro). */
function page_header(string $titulo, string $descricao = '', string $acoesHtml = ''): void
{
    ?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= e($titulo) ?></h1>
            <?php if ($descricao !== ''): ?><p class="page-description"><?= e($descricao) ?></p><?php endif; ?>
        </div>
        <?php if ($acoesHtml !== ''): ?><div class="page-actions"><?= $acoesHtml ?></div><?php endif; ?>
    </div>
    <?php
}

function botao_voltar(string $href, string $rotulo = 'Voltar'): string
{
    return '<a href="' . e($href) . '" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ' . e($rotulo) . '</a>';
}

function empty_state(string $mensagem, string $icone = 'bi-inbox', string $acaoHtml = ''): string
{
    return '<div class="empty-state"><i class="bi ' . e($icone) . '"></i><p>' . e($mensagem) . '</p>' . $acaoHtml . '</div>';
}

function badge_status(string $status): string
{
    [$rotulo, $classe] = match ($status) {
        'futuro' => ['Futuro', 'neutral'],
        'andamento' => ['Em andamento', 'info'],
        'concluido' => ['Concluído', 'success'],
        'ativo' => ['Ativo', 'success'],
        'inativo' => ['Inativo', 'neutral'],
        'pendente' => ['Pendente', 'warning'],
        default => [$status, 'neutral'],
    };
    return '<span class="status status-' . $classe . '">' . e($rotulo) . '</span>';
}

function stat_tile(string $rotulo, string $valor, string $icone, string $href = ''): string
{
    $tag = $href !== '' ? 'a href="' . e($href) . '"' : 'div';
    $fecha = $href !== '' ? 'a' : 'div';
    return '<' . $tag . ' class="stat"><span class="stat-icon"><i class="bi ' . e($icone) . '"></i></span>'
        . '<span class="stat-body"><span class="stat-value">' . e($valor) . '</span><span class="stat-label">' . e($rotulo) . '</span></span></' . $fecha . '>';
}

/** Formulário POST de uma linha só (botão), com CSRF e confirmação opcional. */
function form_acao(string $action, array $campos, string $rotuloHtml, string $classe = 'btn btn-sm btn-outline-danger', string $confirmar = ''): string
{
    $html = '<form method="POST" action="' . e($action) . '" class="d-inline"'
        . ($confirmar !== '' ? ' data-confirm="' . e($confirmar) . '"' : '') . '>' . csrf_field();
    foreach ($campos as $nome => $valor) {
        $html .= '<input type="hidden" name="' . e($nome) . '" value="' . e($valor) . '">';
    }
    return $html . '<button type="submit" class="' . e($classe) . '">' . $rotuloHtml . '</button></form>';
}

/** Paginação simples preservando a query string atual. */
function paginacao(int $pagina, int $totalPaginas): string
{
    if ($totalPaginas <= 1) {
        return '';
    }
    $link = function (int $p) {
        return '?' . http_build_query(array_merge($_GET, ['pagina' => $p]));
    };
    $html = '<nav aria-label="Paginação"><ul class="pagination">';
    $html .= '<li class="page-item' . ($pagina <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . e($link(max(1, $pagina - 1))) . '" aria-label="Anterior"><i class="bi bi-chevron-left"></i></a></li>';
    for ($p = 1; $p <= $totalPaginas; $p++) {
        $html .= '<li class="page-item' . ($p === $pagina ? ' active' : '') . '"><a class="page-link" href="' . e($link($p)) . '">' . $p . '</a></li>';
    }
    $html .= '<li class="page-item' . ($pagina >= $totalPaginas ? ' disabled' : '') . '"><a class="page-link" href="' . e($link(min($totalPaginas, $pagina + 1))) . '" aria-label="Próxima"><i class="bi bi-chevron-right"></i></a></li>';
    return $html . '</ul></nav>';
}

/** Calcula [pagina, offset, totalPaginas] a partir de ?pagina= e do total. */
function paginar(int $total, int $porPagina = 20): array
{
    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $pagina = min($totalPaginas, max(1, (int) ($_GET['pagina'] ?? 1)));
    return [$pagina, ($pagina - 1) * $porPagina, $totalPaginas];
}
