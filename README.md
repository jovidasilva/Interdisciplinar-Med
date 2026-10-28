# InterMed — Sistema de Gestão de Internato Médico

Sistema web para gerenciar o internato de um curso de Medicina: cadastro de alunos, preceptores e coordenação, organização de módulos/rodízios entre unidades de saúde, montagem de grade de horários e aplicação de avaliações de preceptor sobre aluno.

Desenvolvido como projeto interdisciplinar universitário (curso de Medicina/Sistemas de Informação — CEUMA) e depois revisado para servir como peça de portfólio: correção de falhas de segurança, organização do código, containerização e documentação.

## Funcionalidades

O sistema tem três papéis de usuário, cada um com seu próprio painel:

**Aluno**
- Consultar horários de atividades
- Consultar notas/avaliações recebidas
- Acompanhar os rodízios em que está inserido

**Preceptor**
- Consultar horários e grupos sob sua responsabilidade
- Acompanhar rodízios
- Avaliar alunos (formulário de avaliação com perguntas parametrizáveis)

**Coordenação**
- Gerenciar usuários (aprovar cadastro — usuários novos entram como "pendente" —, alterar tipo/status, importar em lote via CSV, listagem paginada)
- Gerenciar unidades de saúde e departamentos
- Gerenciar módulos (disciplinas do internato) e associá-los a alunos/preceptores/unidades
- Gerar **ciclos de rodízio** (ver abaixo) e ajustar grupos/subgrupos
- Montar a grade de horários, com detecção de conflito de preceptor e de subgrupo
- Consultar avaliações, gerenciar as perguntas do formulário e ver relatórios

### Como funciona um ciclo de rodízio

1. A coordenação escolhe o **período** (9º–12º), de 2 a 6 **módulos** desse período e uma **janela de datas** por módulo (consecutivas; dá para preencher automaticamente a partir da data de início e das semanas por janela).
2. Os **alunos ativos do período** são sorteados em N grupos (N = nº de módulos) e cada grupo em S subgrupos — tamanhos equilibrados (diferença máxima de 1 aluno).
3. A rotação é um quadrado latino: em cada janela cada grupo está em um módulo diferente, e ao fim do ciclo todo grupo passou por todos os módulos exatamente uma vez.
4. As matrículas dos alunos nos módulos (usadas pelas avaliações) são criadas automaticamente.
5. Ciclos do mesmo período com datas sobrepostas são bloqueados. Excluir um ciclo remove seus grupos, rodízios e horários (matrículas e avaliações são mantidas).

A lógica fica em `includes/rodizios.php` e é coberta por `tests/RodiziosTest.php`.

**Conta e acesso**
- Login com bloqueio temporário após tentativas seguidas incorretas (rate limiting)
- Recuperação de senha por e-mail ("esqueci minha senha")

## Stack

- **Backend:** PHP 8 (sem framework, orientado a includes/roteamento simples por `?page=`), mysqli com prepared statements
- **Banco de dados:** MySQL 8
- **Frontend:** Bootstrap 5.3 (tema claro/escuro), Bootstrap Icons, SweetAlert2, JavaScript puro; design system próprio em `css/style.css` (tokens mapeados nas variáveis do Bootstrap)
- **Dependências:** Composer (PHPMailer para envio de e-mail, PHPUnit para testes)
- **Infraestrutura local:** Docker + Docker Compose (app + MySQL + phpMyAdmin + Mailpit)
- **CI:** GitHub Actions (lint PHP, testes PHPUnit, build da imagem Docker)

## Como rodar localmente (Docker)

Pré-requisito: [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado.

```bash
git clone https://github.com/jovidasilva/Interdisciplinar-Med.git
cd Interdisciplinar-Med
cp .env.example .env
docker compose up --build
```

Isso sobe quatro serviços:

| Serviço      | URL                              | Descrição                          |
|--------------|-----------------------------------|-------------------------------------|
| app          | http://localhost:8080             | Aplicação PHP                       |
| db           | localhost:3306                    | MySQL (schema criado automaticamente) |
| phpmyadmin   | http://localhost:8081             | Interface de administração do banco |
| mailpit      | http://localhost:8025             | Caixa de entrada fake — veja aqui os e-mails de "esqueci minha senha" enviados em desenvolvimento |

O banco já sobe com o schema completo (incluindo as migrações), as perguntas de avaliação padrão e um usuário de coordenação para teste imediato:

- **Login:** `admin`
- **Senha:** `admin123`

> Dados de exemplo (alunos e preceptor fictícios) ficam em `db/seed/*.csv` — dá pra importá-los pela própria tela de "Importar usuários" da coordenação, usada para testar o fluxo de import em lote do sistema.

## Como rodar sem Docker

1. Suba um MySQL local e crie o schema a partir de `db/schema.sql`.
2. Configure as variáveis de ambiente (copie `.env.example` para `.env` e ajuste `DB_HOST`/`DB_USER`/`DB_PASS`/`DB_NAME` para o seu ambiente).
3. Rode `composer install`.
4. Sirva a raiz do projeto com Apache/Nginx (ou `php -S localhost:8000` para um teste rápido).

## Estrutura do projeto

```
cadastro_e_login/   Login, cadastro, logout, recuperação de senha
cfg/                 Configuração de conexão com o banco (lê variáveis de ambiente)
css/, img/, script/  Estáticos (script/app.js: confirmações, flash, tema, menu)
includes/            app.php (bootstrap de toda página: sessão, acesso por papel,
                     flash, helpers de banco), layout.php (shell e componentes),
                     rodizios.php (geração de ciclos), csv.php, csrf.php,
                     rate-limit.php, mailer.php, validacao.php, perfil
pages/
  aluno/             Telas do papel aluno
  preceptor/          Telas do papel preceptor
  coordenacao/         Telas do papel coordenação, organizadas por domínio
                       (usuarios, modulos, unidades, grupos, horarios, rodizios, avaliacoes)
db/
  schema.sql          Schema completo do banco
  migrations/          Alterações incrementais (002 reset de senha, 003 rate limiting,
                       004 nota decimal, 005 ciclos de rodízio + integridade)
  seed/                Usuário admin padrão + CSVs de exemplo
tests/               Testes automatizados (PHPUnit)
.github/workflows/   CI (lint, testes, build Docker)
```

## Modelo de dados (resumo)

- `usuarios`: tabela única para todos os papéis, discriminada pelo campo `tipo` (0=aluno, 1=preceptor, 2=coordenação, 3=coordenação+preceptor)
- `unidades` / `departamentos`: unidades de saúde onde ocorre o internato
- `modulos`: disciplinas do internato, vinculadas a um período letivo
- `grupos` / `subgrupos` / `alunos_subgrupos`: divisão dos alunos de um período para fins de rodízio
- `rodizio_ciclos`: cada geração de rodízios (período + módulos + janelas); dono dos seus grupos e rodízios
- `rodizios` / `rodizios_subgrupos`: janelas de tempo em que um subgrupo cursa um módulo
- `horarios`: grade semanal (unidade + módulo + departamento + preceptor + subgrupo)
- `avaliacoes` / `perguntas_avaliacoes` / `avaliacoes_respostas`: avaliações de preceptor sobre aluno (uma por aluno × módulo × preceptor; nota = média das respostas), com perguntas parametrizáveis

## Segurança

Este projeto passou por uma revisão de segurança que endereçou, entre outros pontos:

- Controle de acesso ausente em páginas de ação acessíveis diretamente por URL
- Um caso de IDOR (edição de dados de outro usuário sem autorização)
- XSS armazenado em uma tela de upload de CSV
- Mensagens de erro que expunham detalhes internos do banco de dados
- Credenciais de banco hardcoded no código-fonte (agora via variáveis de ambiente)
- Token CSRF em todos os formulários que alteram dados
- Rate limiting no login (bloqueio temporário após tentativas seguidas incorretas)

- Toda ação que altera dados é `POST` com token CSRF (não há mais exclusão por link `GET`)
- Login rejeita contas desativadas e regenera o ID de sessão (sem fixação de sessão)
- Autorização por papel centralizada (`exigir_acesso()` em `includes/app.php`); preceptor só vê/avalia alunos dos próprios módulos
- Erros inesperados são registrados no log e exibidos como mensagem genérica

É um projeto acadêmico/de portfólio; não foi feita uma auditoria completa de penetração e não é recomendado para produção sem uma nova revisão de segurança.

## Testes e CI

```bash
docker compose exec app composer update   # regenera o composer.lock com o PHPUnit (rodar 1x)
docker compose exec app vendor/bin/phpunit
```

O GitHub Actions (`.github/workflows/ci.yml`) roda automaticamente a cada push/PR: lint de todo arquivo PHP, testes PHPUnit e build da imagem Docker.

## Licença

Distribuído sob a licença MIT — veja [LICENSE](LICENSE).

## Atualizando um banco existente

O Docker só roda os scripts de `db/` na primeira criação do volume. Para aplicar as migrações novas num banco que já existe:

```bash
docker compose exec -T db mysql -uroot -proot < db/migrations/004_nota_decimal.sql
docker compose exec -T db mysql -uroot -proot < db/migrations/005_ciclos_e_integridade.sql
```

A 005 remove duplicatas das tabelas de associação antes de criar as chaves únicas (inclusive `usuarios.login`); se houver logins repetidos, resolva-os antes de rodar.
