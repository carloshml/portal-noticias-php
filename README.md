# Portal de Notícias

Aplicação web em PHP para publicação e leitura de notícias, com autenticação de usuários, editor de notícias, upload de imagens, tags normalizadas e navegação por filtros.

## Visão geral

O projeto funciona como um portal público de notícias e um painel de gestão:

- Visitantes leem as notícias na home e na página `/noticias`.
- Usuários autenticados publicam e editam notícias.
- O painel permite gerenciar notícias e usuários.
- As tags possuem tabela própria e são reutilizadas entre notícias.

## Funcionalidades

- Home com as 5 notícias mais recentes, destaque e cards.
- Página pública de leitura em `/noticias`:
  - notícia principal com texto completo;
  - 3 notícias secundárias;
  - paginação de 4 notícias por página;
  - filtro por tag via `?tag=`;
  - leitura de uma notícia específica via `?noticia=`.
- Navegação por tags: qualquer tag exibida leva à listagem filtrada.
- Editor único para criação e edição de notícias.
- Sugestão das 5 tags usadas mais recentemente no editor.
- Upload de imagem da notícia.
- Cadastro, login e logout de usuários.
- Gerenciamento de usuários com validação de login e e-mail únicos.
- Paginação da listagem de notícias do painel (6 por página).
- Layout responsivo com Bootstrap 5.

## Tecnologias

- PHP 8.1+ (testado com PHP 8.2)
- MySQL 5.7+ / MariaDB 10.4+
- PDO para acesso ao banco
- Apache 2.4 com `mod_rewrite`
- Bootstrap 5, Font Awesome e Google Fonts via CDN
- JavaScript e CSS puro no front-end

## Requisitos

- XAMPP, WAMP ou outro ambiente com Apache, PHP e MySQL/MariaDB.
- Extensões PHP `pdo_mysql` e `mbstring`.
- `mod_rewrite` habilitado no Apache.
- `AllowOverride All` para a pasta do projeto.
- Acesso à internet para carregar Bootstrap, Font Awesome e a fonte Inter via CDN.

## Instalação

1. Copie o projeto para a pasta pública do Apache, por exemplo:

```text
C:\xampp\htdocs\contatos-php
```

2. Crie o banco de dados:

```sql
CREATE DATABASE phpCRUD CHARACTER SET utf8 COLLATE utf8_unicode_ci;
```

3. Importe o schema:

```bash
mysql -u root phpCRUD < config/tables.sql
```

Ou importe `config/tables.sql` pelo phpMyAdmin.

4. Configure a conexão em `config/banco.php`:

```php
private static $dbNome = 'phpCRUD';
private static $dbHost = 'localhost';
private static $dbUsuario = 'root';
private static $dbSenha = '';
```

5. Confira a configuração do Apache:

- `mod_rewrite` carregado;
- `AllowOverride All` para `C:\xampp\htdocs`;
- o arquivo `.htaccess` da raiz do projeto deve estar presente.

6. Acesse:

```text
http://localhost/contatos-php/
```

A rota amigável de leitura fica em:

```text
http://localhost/contatos-php/noticias
```

## Atualização de uma base existente

### Normalização das tags

Bases criadas antes da normalização devem executar:

```bash
mysql -u root phpCRUD < config/migrations/2026-10-29-normalize-tags.sql
```

A migração é idempotente:

- cria `tags` e `noticia_tags`;
- migra `tag1`, `tag2` e `tag3` para a nova estrutura;
- mantém as colunas antigas para compatibilidade;
- não remove nem duplica dados existentes.

### Unicidade de login e e-mail

O `config/tables.sql` já cria os índices únicos. Em uma base antiga, depois de remover possíveis duplicidades, aplique:

```sql
ALTER TABLE pessoa
  ADD UNIQUE KEY uq_pessoa_login (login),
  ADD UNIQUE KEY uq_pessoa_email (email);
```

> Faça backup do banco antes de executar qualquer migração ou alteração de schema.

## Rotas principais

| Rota | Descrição | Acesso |
| --- | --- | --- |
| `/` ou `/index.php` | Home com últimas notícias | Público |
| `/noticias` | Página pública de leitura e paginação | Público |
| `/noticias?tag=<nome>` | Listagem filtrada por tag | Público |
| `/noticias?noticia=<id>` | Leitura de uma notícia específica | Público |
| `/noticias?pagina=<n>` | Paginação da página pública | Público |
| `/views/noticia-form.php` | Editor de nova notícia | Autenticado |
| `/views/noticia-form.php?noticia_id=<id>` | Editor de notícia existente | Autenticado |
| `/views/listar_noticias.php` | Listagem/gestão de notícias | Autenticado |
| `/views/home.php` | Painel do usuário | Autenticado |
| `/views/salvar_usuarios.php` | Gerenciamento de usuários | Autenticado |
| `/views/usuario-inscrevase.php` | Cadastro público de usuário | Público |

Rotas antigas preservadas por redirecionamento:

- `/views/escrever_noticia.php` → `noticia-form.php`
- `/views/noticia-read.php?noticia_id=<id>` → `/noticias?noticia=<id>`

## Estrutura do projeto

```text
.
├── index.php                  # Home pública
├── noticias.php               # Leitura, filtro por tag e paginação
├── .htaccess                  # Rota amigável /noticias
├── assets/
│   ├── css/
│   │   ├── home.css
│   │   ├── noticias.css
│   │   └── style.css
│   └── js/
├── config/
│   ├── banco.php              # Conexão PDO
│   ├── tables.sql             # Schema para instalações novas
│   └── migrations/
│       └── 2026-10-29-normalize-tags.sql
├── controllers/               # Endpoints auxiliares
├── DAO/
│   ├── noticia.php
│   ├── tag.php
│   └── usuarios.php
├── services/                  # Criação/edição, login e logout
└── views/
    ├── partials/              # Head, header, footer, modal e scripts
    ├── noticia-form.php
    ├── noticia-form.js
    ├── listar_noticias.php
    ├── home.php
    ├── salvar_usuarios.php
    └── usuario-inscrevase.php
```

## Modelo de dados

### `pessoa`

Usuários do sistema:

- `id`
- `nome`
- `login` (único)
- `endereco`
- `telefone`
- `email` (único)
- `sexo`
- `senha`

### `noticia`

Notícias publicadas:

- `id`
- `titulo`
- `texto`
- `tag1`, `tag2`, `tag3` — colunas legadas mantidas para compatibilidade
- `id_autor` — FK para `pessoa`
- `foto` — imagem em `LONGBLOB`
- `data_criacao`

### `tags`

Cadastro centralizado das tags:

- `id`
- `nome` (único)
- `ultima_utilizacao`
- `created_at`

### `noticia_tags`

Relação N:N entre notícias e tags:

- `noticia_id` — FK para `noticia`
- `tag_id` — FK para `tags`
- chave primária composta (`noticia_id`, `tag_id`)

As tags exibidas no site são lidas de `tags` + `noticia_tags`. As colunas `tag1..3` continuam sendo atualizadas em conjunto para manter compatibilidade com consultas antigas.

## Consultas e comportamentos relevantes

- A página pública carrega apenas os registros da página atual com `LIMIT`/`OFFSET`.
- O filtro por tag usa `INNER JOIN` com `noticia_tags`, sem carregar todas as notícias no navegador.
- A paginação preserva o filtro na URL.
- Ao abrir uma notícia a partir de uma listagem filtrada, o filtro é mantido na URL e no link de retorno.
- O editor carrega a notícia pelo `noticia_id` no servidor, sem transportar o registro em JSON pela URL.
- Após criar ou editar, a aplicação usa POST/Redirect/Get e mensagens de sessão.

## Segurança e limitações conhecidas

- As senhas atuais são armazenadas com MD5. O ideal é migrar para `password_hash()` e `password_verify()`.
- Os formulários ainda não possuem token CSRF.
- Não existe controle de perfil/permissão: qualquer usuário autenticado pode acessar as telas de gestão.
- As imagens são armazenadas no banco em `LONGBLOB`; para muitos arquivos, avaliar storage externo.
- O upload valida o envio, mas não possui limite explícito de tamanho/tipo no servidor.
- As credenciais padrão do XAMPP (`root` sem senha) devem ser alteradas em produção.
- O `.htaccess` depende de Apache com `mod_rewrite`; o servidor embutido do PHP não aplica essas regras.
- Não há suíte de testes automatizados. A validação atual é manual e com `php -l`.

## Próximos passos sugeridos

- Criar um executor de migrações com tabela `schema_migrations`.
- Migrar as senhas para `password_hash`/`password_verify`.
- Adicionar CSRF e validação de permissões.
- Remover as colunas legadas `tag1..3` após período de validação.
- Criar testes automatizados para os fluxos de notícias, tags e usuários.
- Avaliar paginação por cursor para bases muito grandes.
