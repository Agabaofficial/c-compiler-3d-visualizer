# CompilerHub – Setup Guide

This guide walks you through setting up the full backend stack (MySQL, optional Redis) alongside the existing frontend-only installation.

---

## Prerequisites

| Requirement | Minimum Version | Notes |
|-------------|-----------------|-------|
| PHP | 7.4 | `pdo`, `pdo_mysql` extensions required |
| MySQL | 5.7 / 8.0+ | MariaDB 10.3+ also works |
| Apache / Nginx | any | `mod_rewrite` enabled for Apache |
| Redis | 5.x+ | *Optional* – enables result caching |
| GCC / Clang | any | For C/C++ compilation |
| Java JDK | 11+ | For Java visualizations |
| Go | 1.18+ | Optional – for Go visualizations |

---

## 1. Clone the Repository

```bash
git clone https://github.com/Agabaofficial/c-compiler-3d-visualizer.git
cd c-compiler-3d-visualizer
```

---

## 2. Configure Environment Variables

```bash
cp config/.env.example config/.env
```

Open `config/.env` and fill in your values:

```env
# ── Database ──────────────────────────────────────────────────────────
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=your_password
DB_NAME=compilerhub

# ── JWT (change this to a random 32+ character string) ────────────────
JWT_SECRET=change_me_to_something_long_and_random_32chars
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800

# ── Redis (optional) ──────────────────────────────────────────────────
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# ── API ───────────────────────────────────────────────────────────────
API_URL=http://localhost/api/v1
FRONTEND_URL=http://localhost

# ── Security ──────────────────────────────────────────────────────────
CORS_ALLOWED_ORIGINS=http://localhost,http://localhost:3000
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=60

# ── Logging ───────────────────────────────────────────────────────────
LOG_LEVEL=error
LOG_FILE=logs/error.log
ACCESS_LOG_FILE=logs/access.log
DEBUG_MODE=false

# ── Admin credentials (used by admin/index.php) ───────────────────────
ADMIN_USER=admin
ADMIN_PASS=Change_Me_Before_Deploy!
```

> **Security note** – change `ADMIN_PASS` and `JWT_SECRET` before exposing the project to the internet.

---

## 3. Create the MySQL Database

```bash
mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS compilerhub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
SQL
```

Import the schema:

```bash
mysql -u root -p compilerhub < database/schema.sql
```

---

## 4. Create an Admin User

After importing the schema, insert the first admin account:

```bash
php -r "
require 'src/Config/Constants.php';
\App\Config\Constants::load();
require 'src/Config/Database.php';
\$db   = \App\Config\Database::getInstance();
\$hash = password_hash(getenv('ADMIN_PASS') ?: 'Admin@12345!', PASSWORD_BCRYPT);
\$stmt = \$db->prepare(
    'INSERT IGNORE INTO users (username, email, password_hash, role)
     VALUES (?, ?, ?, \"admin\")'
);
\$stmt->execute([
    getenv('ADMIN_USER') ?: 'admin',
    'admin@compilerhub.dev',
    \$hash,
]);
echo 'Admin user created.' . PHP_EOL;
"
```

Or use the equivalent SQL directly (generate the hash first with PHP):

```bash
# Generate the bcrypt hash for your chosen password:
php -r "echo password_hash('YourStrongPassword', PASSWORD_BCRYPT) . PHP_EOL;"
```

```sql
INSERT IGNORE INTO users (username, email, password_hash, role)
VALUES (
  'admin',
  'admin@compilerhub.dev',
  -- paste the hash produced by the command above:
  '$2y$12$REPLACE_WITH_BCRYPT_HASH_OUTPUT',
  'admin'
);
```

---

## 5. Set File Permissions

```bash
chmod 755 logs/
chmod 644 logs/.gitkeep
chmod 750 config/
chmod 640 config/.env
```

---

## 6. Web Server Configuration

### Apache (`mod_rewrite` must be enabled)

Add or create an `.htaccess` file at the project root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Route all /api/v1/* requests through api/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/v1/(.*)$ api/index.php [QSA,L]

    # Route /admin/* through admin/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^admin/(.*)$ admin/index.php?request=$1 [QSA,L]
</IfModule>
```

### Nginx

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/c-compiler-3d-visualizer;
    index index.php;

    location /api/v1/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }

    location /admin/ {
        try_files $uri $uri/ /admin/index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 7. Install Redis (Optional)

### Ubuntu / Debian

```bash
sudo apt-get update && sudo apt-get install -y redis-server
sudo systemctl enable --now redis-server
redis-cli ping   # should print PONG
```

### macOS (Homebrew)

```bash
brew install redis
brew services start redis
redis-cli ping
```

---

## 8. Quick Smoke Test

### Start the built-in PHP server (development only)

```bash
php -S localhost:8000
```

### Test the API

```bash
# Register a user
curl -s -X POST http://localhost:8000/api/index.php \
  -H "Content-Type: application/json" \
  -d '{"route":"auth/register","username":"testuser","email":"test@example.com","password":"Test@1234"}'

# Login
curl -s -X POST http://localhost:8000/api/index.php \
  -H "Content-Type: application/json" \
  -d '{"route":"auth/login","email":"test@example.com","password":"Test@1234"}'
```

### Access the Admin Panel

Open `http://localhost:8000/admin/` in your browser.  
Default credentials are set via `ADMIN_USER` / `ADMIN_PASS` in `config/.env`.

---

## 9. Security Hardening (Production)

| Item | Action |
|------|--------|
| HTTPS | Obtain a TLS certificate (Let's Encrypt) and redirect all HTTP traffic |
| JWT Secret | Set `JWT_SECRET` to a random 64-character string |
| Admin password | Change `ADMIN_PASS` immediately after first login |
| `DEBUG_MODE` | Set to `false` in `config/.env` |
| File permissions | Ensure `config/.env` is `640` (not world-readable) |
| Log rotation | Configure `logrotate` for `logs/*.log` |
| Database | Use a dedicated MySQL user with limited privileges |

---

## 10. Troubleshooting

| Problem | Solution |
|---------|----------|
| `Database connection failed` | Check `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` in `config/.env` |
| `logs/rl/` permission error | Run `mkdir -p logs/rl && chmod 700 logs/rl` |
| Admin login always fails | Verify `ADMIN_USER` and `ADMIN_PASS` are set in the environment or `config/.env` |
| JWT token invalid | Ensure `JWT_SECRET` is at least 32 characters |
| CORS errors | Add your frontend origin to `CORS_ALLOWED_ORIGINS` |
| Redis not connecting | Run `redis-cli ping`; if it fails, start the Redis service |
