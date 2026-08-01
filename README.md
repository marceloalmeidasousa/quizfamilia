# 📌 Quiz em Família

Quiz multiplayer para jogar em família: modo solo, ao vivo e desafio X1. Suporta marcas **Quiz em Família** (`quizemfamilia.com.br`) e **Anima Quiz** (`animaquiz.com.br`).

**Repositório:** [github.com/marceloalmeidasousa/quizfamilia](https://github.com/marceloalmeidasousa/quizfamilia)

Ecossistema Laravel:

- [Laravel](https://github.com/laravel/laravel) 13
- [Laravel Sail](https://github.com/laravel/sail) (PHP **8.5** no Docker)
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission)

Para o front-end estamos utilizando:

- [Vite](https://github.com/vitejs/vite)
- [Tailwind CSS](https://tailwindcss.com/) 4
- [Flowbite](https://flowbite.com/)

### 📋 Requisitos

- [Docker](https://docs.docker.com/get-docker/) e Docker Compose v2
- [Git](https://git-scm.com/)
- PHP **8.3+** (runtime Sail do projeto: **8.5**)
- Node **20+** (recomendado **22 LTS** ou superior)

No Linux, o utilizador deve poder executar Docker sem `sudo` (grupo `docker`).

### 🔧 Como instalar (desenvolvimento com Sail)

Atalho (recomendado):

```bash
bash scripts/dev.sh
```

Isso cria o `.env` se faltar, instala o Composer via Docker, sobe os containers, roda migrations e inicia o Vite.

Passo a passo manual:

- Clone o projeto

    ```bash
    git clone git@github.com:marceloalmeidasousa/quizfamilia.git
    cd quizfamilia
    ```

- Copie o arquivo `.env.example`

    ```bash
    cp .env.example .env
    ```

    Ajuste, se necessário, a porta da aplicação no `.env`:

    ```env
    APP_URL=http://localhost
    APP_PORT=80
    VITE_PORT=5173
    ```

    > Se a porta 80 estiver ocupada, use por exemplo `APP_PORT=8080` e `APP_URL=http://localhost:8080`.

- Instale as dependências do Composer usando Docker

    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        composer:2 \
        composer install --ignore-platform-reqs
    ```

- Você pode configurar um alias para o Sail

    ```bash
    alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
    ```

- Inicie o sistema com Docker e Sail

    ```bash
    sail up -d
    ```

    Na primeira execução, se necessário:

    ```bash
    sail build --no-cache
    ```

- Gere a chave da aplicação

    ```bash
    sail artisan key:generate
    ```

- Execute as migrations com seeders

    ```bash
    sail artisan migrate --seed
    ```

    Para zerar o banco local e reseedar:

    ```bash
    bash scripts/dev.sh --fresh
    # ou: sail artisan migrate:fresh --seed
    ```

    > **Atenção:** `migrate:fresh` apaga todas as tabelas do banco.

- Instale as dependências do Node

    ```bash
    sail npm install
    ```

- Gere o build da aplicação (produção local) **ou** use o Vite em modo dev

    ```bash
    sail npm run build
    # desenvolvimento com hot reload:
    sail npm run dev
    ```

- Acesse a aplicação

    - Site: `http://localhost` (ou a porta definida em `APP_PORT`)
    - Painel admin: `http://localhost/login` → `/painel`
    - Credenciais padrão (`.env`): ver `ADMIN_EMAIL` / `ADMIN_PASSWORD`

### ⚙️ Serviços em desenvolvimento

Filas (`QUEUE_CONNECTION=database` no `.env`), se precisar:

```bash
sail artisan queue:work --tries=3
```

Vite com hot reload (em outro terminal, se não usou `scripts/dev.sh`):

```bash
sail npm run dev
```

Seeders úteis:

```bash
# Perguntas (a partir de resources/data / perguntas.json)
sail artisan db:seed --class=QuestionSeeder

# Usuário admin (ADMIN_* no .env)
sail artisan db:seed --class=AdminUserSeeder
```

### 📦 Ferramentas de desenvolvimento

- Execute os testes (PHPUnit)

    ```bash
    sail artisan test
    ```

- Corrija o estilo de código PHP com Pint

    ```bash
    sail exec laravel.test ./vendor/bin/pint
    ```

- Comandos úteis

    | Objetivo          | Comando                         |
    |-------------------|---------------------------------|
    | Script completo   | `bash scripts/dev.sh`           |
    | Rebuild imagens   | `bash scripts/dev.sh --build`   |
    | Fresh + seed      | `bash scripts/dev.sh --fresh`   |
    | Shell no PHP      | `sail shell` / `dev.sh --shell` |
    | Artisan           | `sail artisan ...`              |
    | Composer          | `sail composer ...`             |
    | Parar containers  | `bash scripts/dev.sh --stop`    |
    | Derrubar tudo     | `bash scripts/dev.sh --down`    |
    | Logs              | `sail logs -f`                  |

## 🚀 Pronto, ambiente configurado!

### 🌐 Deploy em produção (aaPanel)

Para colocar o sistema em produção num servidor com **aaPanel** (Nginx + PHP-FPM + MySQL), com extensões PHP, o que ativar/desativar, `.env`, scripts e checklist:

- **[docs/DEPLOY-AAPANEL.md](docs/DEPLOY-AAPANEL.md)** — guia completo de produção no aaPanel

Scripts prontos no repositório:

| Script | Uso |
|--------|-----|
| `bash scripts/install.sh` | Primeira instalação no servidor |
| `bash scripts/deploy.sh` | Deploys seguintes (após `git pull`) |
| `bash scripts/dev.sh` | Ambiente local com Sail |

### 🏷️ Marcas (multi-domínio)

| Domínio | Marca |
|---------|--------|
| `quizemfamilia.com.br` | Quiz em Família |
| `animaquiz.com.br` | Anima Quiz |
| localhost / outros | Quiz em Família (padrão) |

Forçar marca no local: `BRAND_FORCE=quizfamilia` ou `BRAND_FORCE=animaquiz` no `.env`.

### 📣 Google AdSense

No `.env` de produção:

```env
ADSENSE_ENABLED=true
ADSENSE_CLIENT=ca-pub-2230318270974880
```

O snippet principal do AdSense fica no `<head>` do layout (`resources/views/layouts/app.blade.php`). Após alterar o `.env`, rode `php artisan optimize:clear` (ou o `deploy.sh`).

Para mais chaves de ambiente, consulte o ficheiro `.env.example`.
