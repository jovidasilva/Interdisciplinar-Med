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
- Gerenciar usuários (aprovar cadastro, alterar tipo/status, importar em lote via CSV)
- Gerenciar unidades de saúde e departamentos
- Gerenciar módulos (disciplinas do internato) e associá-los a alunos/preceptores/unidades
- Gerenciar grupos e subgrupos de alunos
- Gerar e visualizar rodízios entre módulos e grupos
- Montar a grade de horários
- Consultar avaliações e relatórios

## Stack

- **Backend:** PHP 8 (sem framework, orientado a includes/roteamento simples por `?page=`), mysqli com prepared statements
- **Banco de dados:** MySQL 8
- **Frontend:** Bootstrap 5, Bootstrap Icons, SweetAlert2, JavaScript puro
- **Dependências:** Composer (PHPMailer, para uma futura funcionalidade de recuperação de senha por e-mail)
- **Infraestrutura local:** Docker + Docker Compose (app + MySQL + phpMyAdmin)

## Como rodar localmente (Docker)

Pré-requisito: [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado.

```bash
git clone https://github.com/jovidasilva/Interdisciplinar-Med.git
cd Interdisciplinar-Med
cp .env.example .env
docker compose up --build
```

Isso sobe três serviços:

| Serviço      | URL                              | Descrição                          |
|--------------|-----------------------------------|-------------------------------------|
| app          | http://localhost:8080             | Aplicação PHP                       |
| db           | localhost:3306                    | MySQL (schema criado automaticamente) |
| phpmyadmin   | http://localhost:8081             | Interface de administração do banco |

O banco já sobe com a tabela `usuarios` e um usuário de coordenação padrão para teste imediato:

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
cadastro_e_login/   Login, cadastro e logout
cfg/                 Configuração de conexão com o banco (lê variáveis de ambiente)
css/, img/, script/  Estáticos
includes/            Componentes reutilizados (navbar, menus laterais, perfil)
pages/
  aluno/             Telas do papel aluno
  preceptor/          Telas do papel preceptor
  coordenacao/         Telas do papel coordenação, organizadas por domínio
                       (usuarios, modulos, unidades, grupos, horarios, rodizios, avaliacoes)
db/
  schema.sql          Schema completo do banco
  seed/                Usuário admin padrão + CSVs de exemplo
```

## Modelo de dados (resumo)

- `usuarios`: tabela única para todos os papéis, discriminada pelo campo `tipo` (0=aluno, 1=preceptor, 2=coordenação, 3=coordenação+preceptor)
- `unidades` / `departamentos`: unidades de saúde onde ocorre o internato
- `modulos`: disciplinas do internato, vinculadas a um período letivo
- `grupos` / `subgrupos` / `alunos_subgrupos`: divisão dos alunos de um período para fins de rodízio
- `rodizios` / `rodizios_subgrupos`: janelas de tempo em que um subgrupo cursa um módulo
- `horarios`: grade semanal (unidade + módulo + departamento + preceptor + subgrupo)
- `avaliacoes` / `perguntas_avaliacoes` / `avaliacoes_respostas`: avaliações de preceptor sobre aluno, com perguntas parametrizáveis

## Segurança

Este projeto passou por uma revisão de segurança que endereçou, entre outros pontos:

- Controle de acesso ausente em páginas de ação acessíveis diretamente por URL
- Um caso de IDOR (edição de dados de outro usuário sem autorização)
- XSS armazenado em uma tela de upload de CSV
- Mensagens de erro que expunham detalhes internos do banco de dados
- Credenciais de banco hardcoded no código-fonte (agora via variáveis de ambiente)

Ainda assim, é um projeto acadêmico/de portfólio — não foi feita uma auditoria completa de penetração e não há CSRF token nos formulários. Não recomendado para produção sem uma nova revisão de segurança.

## Licença

Distribuído sob a licença MIT — veja [LICENSE](LICENSE).
