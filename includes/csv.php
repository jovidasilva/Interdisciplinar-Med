<?php
// Leitura de CSV enviado por upload, com as validações comuns (tamanho,
// tipo) e detecção automática do separador ("," ou ";", padrão do Excel BR).

const CSV_MAX_BYTES = 5 * 1024 * 1024;

/**
 * Retorna [linhas, erro]. Linhas sem o cabeçalho, já com trim e em UTF-8.
 */
function ler_csv_upload(string $campo): array
{
    $arquivo = $_FILES[$campo] ?? null;
    if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return [[], 'Erro no upload do arquivo.'];
    }
    if ($arquivo['size'] > CSV_MAX_BYTES) {
        return [[], 'O arquivo excede 5 MB.'];
    }
    $mime = mime_content_type($arquivo['tmp_name']);
    if (!in_array($mime, ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'], true)) {
        return [[], 'Envie um arquivo .csv.'];
    }

    $conteudo = (string) file_get_contents($arquivo['tmp_name']);
    $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
    if (!mb_check_encoding($conteudo, 'UTF-8')) {
        $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
    }
    $primeira = strtok($conteudo, "\n");
    $separador = substr_count((string) $primeira, ';') > substr_count((string) $primeira, ',') ? ';' : ',';

    $linhas = [];
    foreach (preg_split('/\r\n|\r|\n/', $conteudo) as $i => $linha) {
        if ($i === 0 || trim($linha) === '') {
            continue;
        }
        $linhas[] = array_map('trim', str_getcsv($linha, $separador));
    }
    return [$linhas, null];
}
