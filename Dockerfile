FROM php:8.4-fpm

# --- System dependencies -----------------------------------------------------
# git/unzip/zip/curl + libs for the PHP extensions, plus OCR/image CLIs
# (tesseract + imagemagick) and poppler-utils (pdftoppm) for rasterizing PDFs.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    tesseract-ocr \
    tesseract-ocr-eng \
    imagemagick \
    poppler-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
# Note: this app runs on MySQL (pdo_mysql, installed above). The MySQL server
# itself runs as a separate service — see docker-compose.yml.

# --- Composer ----------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Node (needed to build the Inertia/React/Vite frontend) ------------------
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copy the application source (see .dockerignore for what is excluded).
COPY . .

# --- PHP dependencies --------------------------------------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# --- App key + frontend build ------------------------------------------------
# An APP_KEY is required for the app to boot. The Wayfinder Vite plugin runs
# `php artisan` during the build, so composer install and .env must come first.
RUN cp -n .env.example .env \
    && php artisan key:generate --force \
    && npm ci \
    && npm run build \
    && npm cache clean --force

# --- Writable dirs -----------------------------------------------------------
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

# Built-in dev server (fine for a hackathon/demo). Migrations run at container
# start, not build time. The loop waits for the MySQL service to accept
# connections before migrating, then serves.
CMD ["sh", "-c", "until php artisan migrate --force; do echo 'Waiting for MySQL...'; sleep 3; done && php artisan serve --host=0.0.0.0 --port=8000"]
