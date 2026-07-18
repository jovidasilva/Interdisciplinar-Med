# Testes automatizados (PHPUnit)

Este diretório contém os testes unitários do projeto, escritos com PHPUnit
11. Como este ambiente de desenvolvimento não tem PHP CLI/Composer
disponível, os arquivos de configuração e os testes foram escritos "a seco"
e ainda **não foram executados**. Rode-os dentro do container Docker que já
existe no projeto (`docker-compose.yml`, serviço `app`).

## Primeira vez (instalar dependências de teste)

O `composer.lock` atual foi gerado antes do PHPUnit ser adicionado ao
`composer.json` (seção `require-dev`), então ele precisa ser regenerado uma
vez:

```bash
docker compose exec app composer update
```

(Isso atualiza o `composer.lock` para incluir `phpunit/phpunit` e deixa o
lockfile consistente com o `composer.json`. Faça isso uma única vez após
estas mudanças, e comite o `composer.lock` resultante.)

Nas execuções seguintes, ou em outra máquina/CI, basta:

```bash
docker compose exec app composer install
```

## Rodando os testes

```bash
docker compose exec app vendor/bin/phpunit
```

Isso usa a configuração em `phpunit.xml` na raiz do projeto (testsuite
apontando para `tests/`, bootstrap `vendor/autoload.php`).

## O que está coberto

- `CsrfTest.php` — `csrf_token()`, `csrf_field()` e `csrf_verify()` de
  `includes/csrf.php`. Como essas funções dependem de `$_SESSION`/`$_POST`,
  os testes simulam "sessões" diferentes apenas reatribuindo `$_SESSION` como
  um array comum entre os casos de teste (a sessão PHP real só é iniciada
  uma vez por processo, na primeira inclusão de `includes/csrf.php`).
- `ValidarEmailTest.php` — `validar_email()`, que foi extraída de
  `includes/alterar-dados.php` para um novo arquivo `includes/validacao.php`
  (função pura, sem mudança de comportamento) para poder ser testada sem
  precisar de sessão logada, conexão com MySQL ou uma requisição HTTP POST
  real, todas coisas de que `alterar-dados.php` depende no seu topo de
  arquivo.

## O que ainda falta

- Testes para o rate limiting: até o momento em que estes testes foram
  escritos, não havia nenhum arquivo de rate limiting (`includes/rate-limit.php`
  ou similar) no repositório — está sendo desenvolvido em paralelo por outro
  processo. Quando esse arquivo existir e expuser funções puras/testáveis
  sem banco de dados, adicione `tests/RateLimitTest.php` seguindo o mesmo
  padrão dos testes acima.
- Testes que dependem de banco de dados (ex.: `alterar_login`,
  `alterar_senha`, `alterar_dados_contato` em `includes/alterar-dados.php`)
  não foram escritos aqui porque exigiriam um MySQL real ou um mock de
  `mysqli`; ficam fora do escopo de testes unitários simples.
