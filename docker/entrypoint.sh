#!/bin/sh
set -e

# `php artisan serve` only forwards environment variables that exist in the
# .env FILE to the underlying `php -S` web server — arbitrary container env
# vars (from docker-compose `environment:`) are dropped. So we sync the
# runtime values into .env before booting the server.
sync_env() {
    var="$1"
    eval "val=\${$var:-}"
    [ -z "$val" ] && return 0

    # Laravel .env values with spaces or special characters must be quoted.
    escaped=$(printf '%s' "$val" | sed 's/"/\\"/g')
    line="${var}=\"${escaped}\""

    if grep -q "^${var}=" .env 2>/dev/null; then
        sed -i "s~^${var}=.*~${line}~" .env
    else
        printf '%s\n' "$line" >> .env
    fi
}

for var in \
    APP_URL \
    DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD \
    GROQ_API_KEY GROQ_MODEL \
    OPENAI_API_KEY \
    OPENAI_MODEL \
    GOOGLE_API_KEY GEMINI_EMBEDDING_MODEL \
    QWDRANT_ENDPOINT QWDRANT_API_KEY QWDRANT_COLLECTION QWDRANT_VECTOR_SIZE \
    MAIL_MAILER MAIL_SCHEME MAIL_HOST MAIL_PORT MAIL_USERNAME MAIL_PASSWORD \
    MAIL_FROM_ADDRESS MAIL_FROM_NAME; do
    sync_env "$var"
done

php artisan config:clear >/dev/null 2>&1 || true

# Wait until MySQL accepts connections — do not retry failed migrations in a
# loop, or a schema error will block the web server from ever starting.
until php -r '
    try {
        new PDO(
            sprintf("mysql:host=%s;port=%s", getenv("DB_HOST") ?: "mysql", getenv("DB_PORT") ?: "3306"),
            getenv("DB_USERNAME") ?: "root",
            getenv("DB_PASSWORD") ?: ""
        );
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
'; do
    echo 'Waiting for MySQL...'
    sleep 3
done

if ! php artisan migrate --force; then
    echo 'WARNING: migrations failed — starting web server anyway so the app stays reachable.'
    echo 'Run: docker compose exec app php artisan migrate --force'
fi

exec php artisan serve --host=0.0.0.0 --port=8000
