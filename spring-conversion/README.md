# Interdisciplinar-Med: Migração PHP para Spring Boot

Este projeto contém a migração das páginas de aluno e preceptor do sistema Interdisciplinar-Med de PHP para Spring Boot, mantendo compatibilidade com o sistema PHP existente.

## Estrutura do Projeto

A estrutura do projeto foi organizada para permitir uma migração gradual do PHP para o Spring Boot:

- `src/main/java/com/interdisciplinar/med`: Código Java do Spring Boot
  - `controller`: Controladores para as páginas de aluno e preceptor
  - `model`: Entidades JPA que representam as tabelas do banco de dados
  - `repository`: Interfaces de repositório para acesso ao banco de dados
  - `service`: Serviços para lógica de negócios
  - `config`: Configurações do Spring Boot

- `src/main/resources`: Recursos do Spring Boot
  - `templates`: Templates Thymeleaf para as páginas HTML
    - `aluno`: Templates para páginas do aluno
    - `preceptor`: Templates para páginas do preceptor
    - `fragments`: Fragmentos reutilizáveis (navbar, menus laterais)
  - `static`: Recursos estáticos (CSS, JS, imagens)
  - `application.properties`: Configurações do Spring Boot

## Integração com o Sistema PHP Existente

A integração entre o sistema PHP existente e o Spring Boot é feita através de:

1. **Adaptador de Login**: O arquivo `login-spring-adapter.php` redireciona os usuários autenticados para as páginas Spring Boot correspondentes.

2. **Mapeamentos de URL Compatíveis**: Os controladores Spring Boot incluem mapeamentos para URLs com extensão `.php` que redirecionam para as URLs Spring Boot correspondentes.

3. **Compartilhamento de Sessão**: As informações de sessão do PHP são passadas para o Spring Boot através de parâmetros de URL.

## Como Executar

1. Instale as dependências do Maven:
   ```
   mvn clean install
   ```

2. Execute o aplicativo Spring Boot:
   ```
   mvn spring-boot:run
   ```

3. Acesse o sistema pelo navegador:
   ```
   http://localhost:8080
   ```

## Fluxo de Autenticação

1. O usuário acessa a página de login PHP (`index.php`)
2. Após autenticação bem-sucedida, o PHP redireciona para:
   - Alunos (tipo 0): `/pages/aluno/home` (Spring Boot)
   - Preceptores (tipo 1): `/pages/preceptor/home` (Spring Boot)
   - Coordenação (tipos 2/3): `/pages/coordenacao/home.php` (PHP - não migrado)

## Banco de Dados

O sistema utiliza o mesmo banco de dados MySQL do sistema PHP existente:
- Nome do banco: `proj_internato`
- Tabelas principais: `usuarios`, `grupos`, `subgrupos`, `modulos`, `avaliacoes`, `horarios`, `rodizios`

