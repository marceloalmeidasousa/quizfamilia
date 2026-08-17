# Deploy em produção no aaPanel

Guia completo para colocar o **Quiz em Família** em produção num servidor Linux com **aaPanel** (Nginx + PHP-FPM + MySQL).

> Convenções usadas abaixo (substitua pelos seus valores):
>
> | Placeholder | Exemplo |
> |-------------|---------|
> | `DOMINIO` | `quizemfamilia.com.br` |
> | `SITE_ROOT` | `/www/wwwroot/quizemfamilia.com.br` |
> | `PHP_BIN` | `/www/server/php/84/bin/php` |
> | Usuário do site | `www` (padrão aaPanel) |

Domínios previstos:

| Domínio | Marca |
|---------|--------|
| `quizemfamilia.com.br` | Quiz em Família |
| `animaquiz.com.br` | Anima Quiz |
| `quizedu.com.br` | Quiz Edu |

Pode apontar todos os domínios para o **mesmo** `SITE_ROOT` (mesma app); a marca é resolvida pelo host.

---

## 1. Stack e versões

| Componente | Versão | Observação |
|------------|--------|------------|
| PHP | **8.3+** (recomendado **8.4**, 64-bit) | `composer.json` exige `^8.3`; o Sail local usa **8.5**. Em produção use **8.3 ou 8.4** do aaPanel. |
| Composer | **≥ 2.2** | Laravel 13 exige `composer-runtime-api ^2.2`. |
| MySQL | **8.0+** (ou MariaDB 10.6+) | Banco padrão do projeto (`DB_CONNECTION=mysql`). |
| Node.js | **20+** (recomendado 22 LTS) | Só para gerar o build de assets (`npm run build`). |
| Git | qualquer recente | `git --version` |
| SSL | Let's Encrypt | Ative no aaPanel antes de finalizar. |

> **Consistência de versão do PHP (crítico no aaPanel):** o `composer install` grava `vendor/composer/platform_check.php` conforme a versão do PHP que executou o Composer. Se depois você rodar `artisan` com **outra** versão, aparece erro de plataforma.
>
> Use a **mesma** versão de PHP no site (PHP-FPM), no CLI, no cron e na fila. Prefira sempre o caminho completo, por exemplo `PHP_BIN=/www/server/php/84/bin/php`.

### 1.1 Componentes a instalar no App Store do aaPanel

Antes de tudo, no aaPanel → **App Store**, instale/ative:

| App | Uso | Status |
|-----|-----|--------|
| **Nginx** | Servidor web do site | **Instalar** |
| **PHP 8.4** (ou 8.3) | Runtime da aplicação | **Instalar** |
| **MySQL 8.0+** (ou MariaDB) | Banco de dados | **Instalar** |
| **PM2 / Node.js version manager** | Node 20+ para `npm run build` | **Instalar** |
| **Supervisor / Process Manager** | Worker de fila (`queue:work`), se usar filas | **Recomendado** |
| **Redis** | Opcional (só se mudar cache/session/fila para Redis) | Opcional |
| **PostgreSQL** | Não usado por este projeto | **Não instalar** (ou ignore) |
| **MongoDB / Memcached** | Não usados | **Não instalar** |

> Se preferir, o build de assets (`npm run build`) pode ser feito noutra máquina e só o resultado (`public/build`) enviado ao servidor — nesse caso o Node não é necessário em produção. Use `bash scripts/deploy.sh --skip-npm`.

---

## 2. Extensões PHP

No aaPanel: **App Store → PHP 8.4 → Settings → Install extensions**.

### 2.1 Ativar (obrigatórias)

| Grupo | Extensões |
|-------|-----------|
| **Obrigatórias (app)** | `pdo_mysql`, `mysqli`, `curl`, `gd`, `mbstring`, `xml`, `zip`, `bcmath`, `fileinfo`, `openssl`, `tokenizer`, `ctype`, `dom`, `iconv`, `filter`, `session`, `json` |
| **Recomendadas** | `opcache`, `exif`, `intl`, `sodium` |

Confirme no CLI:

```bash
PHP_BIN=/www/server/php/84/bin/php
$PHP_BIN -m | grep -Ei 'pdo_mysql|mysqli|curl|gd|mbstring|xml|zip|bcmath|fileinfo|openssl|intl'
```

Todas as obrigatórias devem aparecer. Se `fileinfo` faltar, o `composer install` falha.

### 2.2 Pode deixar desativadas / não instalar

Este projeto **não** precisa de:

| Extensão | Motivo |
|----------|--------|
| `pgsql` / `pdo_pgsql` | Banco é MySQL |
| `mongodb` | Não usado |
| `redis` (extensão PHP) | Só se configurar Redis no `.env` |
| `imagick` | Não obrigatória (`gd` basta) |
| `xdebug` | Só desenvolvimento — **desative em produção** (performance) |
| `swoole` / `roadrunner` | Não usados |

Se o aaPanel listar `mysqli`/`pdo_mysql` como instaláveis, **ative-os**. Não desative MySQL extensions pensando que “não usa” — este app usa MySQL.

### 2.3 `disable_functions` (Composer)

O Composer precisa de `putenv()` e `proc_open()`. Em **PHP → Settings → Disabled functions**, **remova** da lista (se presentes):

```
putenv, proc_open, proc_get_status, proc_close, pcntl_signal, pcntl_alarm
```

Sintoma típico se `putenv` estiver bloqueada:

```
Call to undefined function Composer\XdebugHandler\putenv()
```

### 2.4 php.ini recomendado

Em **PHP → Settings → Configuration** (ou `php.ini`):

```ini
memory_limit = 256M
max_execution_time = 120
post_max_size = 32M
upload_max_filesize = 32M
max_input_vars = 3000
```

Evite `extension=` duplicado (ex.: dois `extension=mbstring`) — gera warning `Module "..." is already loaded`.

Reinicie o **PHP-FPM** após alterar php.ini / extensões / disabled functions.

---

## 3. Banco de dados

No aaPanel: **Databases → Add DB** (MySQL). Anote nome, usuário e senha.

Exemplo:

| Campo | Valor |
|-------|--------|
| Database | `quizfamilia` |
| Username | `quizfamilia` |
| Password | *(senha forte)* |
| Access | `localhost` / `127.0.0.1` |

Charset recomendado: `utf8mb4` / collation `utf8mb4_unicode_ci`.

---

## 4. Site no aaPanel

1. **Website → Add site** com o `DOMINIO` (ex.: `quizemfamilia.com.br`).
2. Defina **PHP version = 8.4** (ou 8.3 — a mesma do CLI).
3. Aponte o **document root** para `SITE_ROOT/public` (não a raiz do projeto).
4. Emita o **SSL** (Let's Encrypt) e ative **Force HTTPS**.
5. (Opcional) Adicione `animaquiz.com.br` e `quizedu.com.br` como domínios extras apontando para o mesmo `public/`.

### 4.1 Nginx — rotas do Laravel (obrigatório)

Sem o `try_files` abaixo, URLs inexistentes caem no **404 do Nginx** em vez da página do Laravel.

No aaPanel: **Website → DOMINIO → Configuração** e garanta:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Logos dos clientes (evita 403 do aaPanel com disable_symlinks)
location ^~ /storage/ {
    alias /www/wwwroot/quizemfamilia.com.br/storage/app/public/;
    access_log off;
    expires 30d;
}

location ~ \.php$ {
    include enable-php-84.conf;
}
```

Ajuste o caminho do `alias` se o `SITE_ROOT` for outro. Depois: **Salvar** e **Reload** do Nginx.

---

## 5. Código-fonte

```bash
cd /www/wwwroot
git clone git@github.com:marceloalmeidasousa/quizfamilia.git quizemfamilia.com.br
cd quizemfamilia.com.br
```

### 5.1 Git como usuário `www`

```bash
git config --global --add safe.directory /www/wwwroot/quizemfamilia.com.br

mkdir -p /home/www/.ssh
ssh-keyscan -t ed25519,rsa github.com >> /home/www/.ssh/known_hosts
chown -R www:www /home/www/.ssh
chmod 700 /home/www/.ssh && chmod 600 /home/www/.ssh/known_hosts
```

Se o clone for por HTTPS com token, configure as credenciais do `www` conforme o seu fluxo.

---

## 6. Binário PHP correto

```bash
php -v                          # pode mostrar 8.3.x do sistema → cuidado
/www/server/php/84/bin/php -v   # use este (ajuste 84/83 conforme instalado)
```

**A) Caminho completo** (recomendado em scripts/cron):

```bash
/www/server/php/84/bin/php artisan ...
```

**B) Alternativa — apontar `php` do sistema para o do aaPanel:**

```bash
update-alternatives --install /usr/bin/php php /www/server/php/84/bin/php 84
update-alternatives --set php /www/server/php/84/bin/php
php -v
```

> Não instale um segundo PHP 8.4 via `apt` num servidor com aaPanel — gera duas `php.ini` e descasamento CLI × FPM.

### 6.1 Composer ≥ 2.2

```bash
/www/server/php/84/bin/php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
/www/server/php/84/bin/php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
/www/server/php/84/bin/php /usr/local/bin/composer --version
```

---

## 7. Configuração `.env`

Na primeira instalação, use o script interativo:

```bash
cd /www/wwwroot/quizemfamilia.com.br
bash scripts/install.sh
```

Ou configure manualmente:

```bash
cp .env.example .env
```

Ajuste no mínimo:

```env
APP_NAME="Quiz em Família"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://quizemfamilia.com.br
APP_TIMEZONE=America/Sao_Paulo

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quizfamilia
DB_USERNAME=quizfamilia
DB_PASSWORD=SUA_SENHA

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# AdSense
ADSENSE_ENABLED=true
ADSENSE_CLIENT=ca-pub-2230318270974880

# OpenAI (quiz personalizado B2B)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini

# Admin (usado pelo AdminUserSeeder)
ADMIN_NAME=Marcelo
ADMIN_EMAIL=seu@email.com
ADMIN_PASSWORD=senha_forte
```

Gere a chave (se `APP_KEY` estiver vazia):

```bash
/www/server/php/84/bin/php artisan key:generate --force
```

> **Nunca** deixe `APP_KEY` vazia em produção — o site cai com `MissingAppKeyException` (erro 500) e o AdSense não consegue verificar o site.

> Para gerar perguntas no painel (**Quiz personalizados**), configure `OPENAI_API_KEY` e mantenha o **queue worker** ativo (passo 10). Sem worker, use temporariamente `QUEUE_CONNECTION=sync` (lento e sujeito a timeout).

---

## 8. Scripts do repositório

### 8.1 Primeira instalação

```bash
cd /www/wwwroot/quizemfamilia.com.br
bash scripts/install.sh
```

Não interativo:

```bash
APP_URL=https://quizemfamilia.com.br \
DB_DATABASE=quizfamilia \
DB_USERNAME=quizfamilia \
DB_PASSWORD='sua_senha' \
bash scripts/install.sh --yes
```

O install faz Composer, build Vite, migrations e seed (perguntas + admin), conforme as flags.

### 8.2 Deploys seguintes

```bash
cd /www/wwwroot/quizemfamilia.com.br
git pull origin main
bash scripts/deploy.sh
```

Ou com pull dentro do script:

```bash
bash scripts/deploy.sh --pull
```

### 8.2.1 Webhook do aaPanel (GitHub / Git)

No aaPanel: **Website → quizemfamilia.com.br → Git** (ou Deploy), configure o repositório e no campo de **comando pós-pull / webhook** cole:

```bash
bash /www/wwwroot/quizemfamilia.com.br/scripts/aapanel-webhook-deploy.sh
```

No GitHub: **Settings → Webhooks** (ou use a URL que o aaPanel gerar) apontando para o push na branch `main`.

O script:
- faz lock (não roda dois deploys ao mesmo tempo)
- chama `scripts/deploy.sh --pull` (composer, npm build, migrate, caches)
- grava log em `storage/logs/deploy-webhook.log`

Teste manual:

```bash
bash /www/wwwroot/quizemfamilia.com.br/scripts/aapanel-webhook-deploy.sh
tail -50 /www/wwwroot/quizemfamilia.com.br/storage/logs/deploy-webhook.log
```

Opções úteis:

| Flag | Efeito |
|------|--------|
| `--pull` | `git pull` dentro do script |
| `--seed` | Roda `db:seed` após migrate |
| `--skip-npm` | Não faz `npm ci` / `npm run build` |
| `--skip-composer` | Não roda Composer |
| `--skip-migrate` | Não roda migrations |
| `--no-maintenance` | Não entra em `artisan down` |

Variáveis opcionais:

```bash
PHP_BIN=/www/server/php/84/bin/php
COMPOSER_BIN=/usr/local/bin/composer
WEB_USER=www
WEB_GROUP=www
BRANCH=main
```

### 8.3 Seeders pontuais (produção)

```bash
# Só perguntas
sudo -u www /www/server/php/84/bin/php artisan db:seed --class=QuestionSeeder --force

# Só admin (lê ADMIN_* do .env)
sudo -u www /www/server/php/84/bin/php artisan db:seed --class=AdminUserSeeder --force
```

Credenciais do admin vêm do `.env` (`ADMIN_EMAIL` / `ADMIN_PASSWORD`). Troque a senha após o primeiro acesso a `/login` → `/painel`.

---

## 9. Scheduler (cron) — opcional

Se no futuro houver tarefas agendadas, no aaPanel: **Cron → Add task** (Shell Script), usuário **`www`**, a cada **1 minuto**:

```bash
cd /www/wwwroot/quizemfamilia.com.br && /www/server/php/84/bin/php artisan schedule:run >> /dev/null 2>&1
```

Hoje o projeto pode funcionar sem cron; mantenha se for usar jobs agendados.

---

## 10. Fila (queue worker) — recomendado

O `.env` usa `QUEUE_CONNECTION=database`. Se houver jobs em background, mantenha um worker ativo.

No aaPanel: **App Store → Supervisor**. Crie um processo:

- **Command:** `/www/server/php/84/bin/php /www/wwwroot/quizemfamilia.com.br/artisan queue:work --sleep=1 --tries=3 --max-time=3600`
- **User:** `www`
- **Directory:** `/www/wwwroot/quizemfamilia.com.br`
- **Processes:** 1

Após cada deploy:

```bash
sudo -u www /www/server/php/84/bin/php artisan queue:restart
```

> Alternativa simples: `QUEUE_CONNECTION=sync` no `.env` (jobs síncronos na requisição).

---

## 11. AdSense e verificação

1. Confirme no HTML da home (`Ctrl+U`) a presença de:
   `pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-...`
2. Site deve responder **200** (não 500).
3. Após mudar `.env`: `php artisan optimize:clear` e `config:cache` (o `deploy.sh` já faz isso).
4. Anúncios Automáticos podem levar de **1 a 48 horas** após o snippet no `<head>`.

---

## 12. Checklist final

- [ ] PHP 8.3/8.4 no site e no CLI (mesmo binário)
- [ ] Extensões ativas: `pdo_mysql`, `mysqli`, `fileinfo`, `gd`, `mbstring`, `curl`, `zip`, `bcmath`, `openssl`, …
- [ ] Extensões desnecessárias desligadas: `xdebug` em produção; sem dependência de `pgsql`
- [ ] `putenv` / `proc_open` fora de `disable_functions`
- [ ] Composer ≥ 2.2
- [ ] Document root em `.../public` + SSL / Force HTTPS
- [ ] `.env` com `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://DOMINIO`, `APP_KEY` preenchida, DB MySQL correto
- [ ] `bash scripts/install.sh` (1ª vez) ou `bash scripts/deploy.sh` sem erros
- [ ] Perguntas e admin seedados; senha do admin trocada
- [ ] Home carrega (HTTP 200) e AdSense snippet visível no HTML
- [ ] (Opcional) Cron `schedule:run` + Supervisor `queue:work`

---

## 13. Problemas comuns

| Sintoma | Causa | Ação |
|---------|-------|------|
| `MissingAppKeyException` / 500 | `APP_KEY` vazia | `php artisan key:generate --force` + `optimize:clear` |
| `Composer detected issues ... require PHP >= X` | CLI ≠ PHP do `composer install` | Use o mesmo `PHP_BIN` (passo 6) |
| `Call to undefined function ... putenv()` | `putenv` em `disable_functions` | Remova e reinicie PHP-FPM (2.3) |
| `ext-fileinfo ... is missing` | Extensão desativada | Ative `fileinfo` (passo 2) |
| `composer-runtime-api ... does not match ^2.2` | Composer antigo | Atualize (6.1) |
| Erro de conexão MySQL | `DB_HOST` errado | Em produção use `127.0.0.1`, não `mysql` (hostname do Sail) |
| Assets quebrados (CSS/JS) | Sem `npm run build` | Rode `deploy.sh` sem `--skip-npm` |
| Alterações não aparecem | Cache de config/views | `artisan optimize:clear` (deploy já faz) |
| AdSense não verifica | Site 500 ou snippet ausente | Corrija APP_KEY; confirme script no `<head>` |
| `git pull`: permission / known_hosts | Dono dos ficheiros / SSH do `www` | Passo 5.1; `chown -R www:www` |
| Jobs não processam | Worker parado | Supervisor / `queue:restart` (passo 10) |
| Logo do cliente não aparece | Symlink `public/storage` ou `disable_symlinks` no Nginx | Passo 4.1 (`location /storage/`); `artisan storage:link --force`; `chown -h www:www public/storage` |
| `styleText` / `node:util` no `npm run build` | Node 18 no PATH; Vite 8 exige 20.19+ ou 22.12+ | Instale Node **22** no aaPanel; `export PATH=/www/server/nodejs/v22/bin:$PATH` e rode o build de novo |
