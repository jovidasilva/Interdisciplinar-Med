<?php
// Ponto de entrada comum de toda página: carrega configuração/conexão,
// sessão, CSRF e os helpers de acesso, escape e mensagens (flash).
// Uso típico no topo de uma página:
//   require_once __DIR__ . '/../../includes/app.php';
//   exigir_acesso(PAPEIS_COORDENACAO);

require_once __DIR__ . '/../cfg/config.php';
require_once __DIR__ . '/csrf.php';

// Obs.: desde o PHP 8.1 o mysqli lança mysqli_sql_exception em erro de SQL
// (ex.: violação de FK numa exclusão) — trate com try/catch onde for esperado.
// O que escapar cai aqui: registra no log e mostra uma mensagem genérica,
// sem expor detalhes internos.
set_exception_handler(function (Throwable $e): void {
    error_log('Erro não tratado: ' . $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!DOCTYPE html><meta charset="UTF-8"><title>Erro</title>'
        . '<div style="font-family:system-ui,sans-serif;max-width:480px;margin:15vh auto;padding:24px;text-align:center">'
        . '<h1 style="font-size:1.4rem">Algo deu errado</h1><p>Não foi possível concluir a operação. Tente novamente; se persistir, avise a coordenação.</p>'
        . '<p><a href="javascript:history.back()">Voltar</a></p></div>';
});

const TIPO_PENDENTE = -1;
const TIPO_ALUNO = 0;
const TIPO_PRECEPTOR = 1;
const TIPO_COORDENACAO = 2;
const TIPO_COORDENACAO_PRECEPTOR = 3;

const PAPEIS_ALUNO = [TIPO_ALUNO];
const PAPEIS_PRECEPTOR = [TIPO_PRECEPTOR, TIPO_COORDENACAO_PRECEPTOR];
const PAPEIS_COORDENACAO = [TIPO_COORDENACAO, TIPO_COORDENACAO_PRECEPTOR];

const TIPOS_USUARIO = [
    TIPO_PENDENTE => 'Pendente de aprovação',
    TIPO_ALUNO => 'Aluno',
    TIPO_PRECEPTOR => 'Preceptor',
    TIPO_COORDENACAO => 'Coordenação',
    TIPO_COORDENACAO_PRECEPTOR => 'Coordenação e Preceptor',
];

const PERIODOS = [9, 10, 11, 12];
const DIAS_SEMANA = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
const SENHA_MIN_LEN = 8;

// Valores das opções de resposta das avaliações (nota de cada pergunta).
const OPCOES_RESPOSTA = [4 => 'Insuficiente', 6 => 'Regular', 8 => 'Bom', 10 => 'Excelente'];

if (!function_exists('e')) {
    function e($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

function usuario_tipo(): ?int
{
    return isset($_SESSION['tipo']) ? (int) $_SESSION['tipo'] : null;
}

function usuario_id(): int
{
    return (int) ($_SESSION['idusuario'] ?? 0);
}

function usuario_tem_papel(array $papeis): bool
{
    return usuario_tipo() !== null && in_array(usuario_tipo(), $papeis, true);
}

function home_do_usuario(?int $tipo = null): string
{
    $tipo ??= usuario_tipo();
    if ($tipo === null) {
        return url('index.php');
    }
    return match (true) {
        $tipo === TIPO_ALUNO => url('pages/aluno/home.php'),
        $tipo === TIPO_PRECEPTOR => url('pages/preceptor/home.php'),
        in_array($tipo, PAPEIS_COORDENACAO, true) => url('pages/coordenacao/home.php'),
        default => url('cadastro_e_login/novocadastro.php'),
    };
}

function flash(string $mensagem, string $tipo = 'success'): void
{
    $_SESSION['flash'][] = ['msg' => $mensagem, 'tipo' => $tipo];
}

function flash_consumir(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

/**
 * Redireciona (com mensagem opcional) e encerra. Funciona mesmo depois de
 * já ter saído HTML (sub-telas incluídas no meio do layout).
 */
function redirecionar(string $destino, ?string $mensagem = null, string $tipo = 'success'): never
{
    if ($mensagem !== null) {
        flash($mensagem, $tipo);
    }
    if (!headers_sent()) {
        header('Location: ' . $destino);
    } else {
        echo '<script>location.replace(' . json_encode($destino) . ');</script>';
    }
    exit();
}

/**
 * Exige usuário logado e, se $papeis for informado, com um desses tipos.
 */
function exigir_acesso(array $papeis = []): void
{
    if (empty($_SESSION['idusuario'])) {
        redirecionar(url('index.php'));
    }
    if ($papeis && !usuario_tem_papel($papeis)) {
        redirecionar(home_do_usuario(), 'Você não tem acesso a essa página.', 'error');
    }
}

/** Exige requisição POST com token CSRF válido. */
function exigir_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }
    csrf_verify_or_die();
}

/** Lista de inteiros positivos a partir de um campo array do POST. */
function ids_do_post(string $campo): array
{
    $valores = $_POST[$campo] ?? [];
    if (!is_array($valores)) {
        return [];
    }
    return array_values(array_unique(array_filter(array_map('intval', $valores), fn($id) => $id > 0)));
}

/** Executa um SELECT preparado e devolve todas as linhas (assoc). */
function db_todos(mysqli $conn, string $sql, string $tipos = '', array $params = []): array
{
    $stmt = $conn->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $linhas;
}

function db_um(mysqli $conn, string $sql, string $tipos = '', array $params = []): ?array
{
    return db_todos($conn, $sql, $tipos, $params)[0] ?? null;
}

/** Executa INSERT/UPDATE/DELETE preparado; devolve linhas afetadas. */
function db_exec(mysqli $conn, string $sql, string $tipos = '', array $params = []): int
{
    $stmt = $conn->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $afetadas = $stmt->affected_rows;
    $stmt->close();
    return $afetadas;
}

function data_br(?string $data): string
{
    return $data ? date('d/m/Y', strtotime($data)) : '—';
}

function hora_curta(?string $hora): string
{
    return $hora ? substr($hora, 0, 5) : '—';
}

/** Status de um intervalo de datas em relação a hoje: futuro|andamento|concluido. */
function status_periodo(string $inicio, string $fim): string
{
    $hoje = date('Y-m-d');
    return $hoje < $inicio ? 'futuro' : ($hoje > $fim ? 'concluido' : 'andamento');
}

require_once __DIR__ . '/layout.php';
