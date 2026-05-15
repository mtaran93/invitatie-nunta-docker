FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libmagickwand-dev \
    libheif-dev \
    imagemagick \
    zip \
    unzip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Allow ImageMagick to read HEIC (Debian disables HEIC/HEIF by default in policy.xml)
RUN for f in /etc/ImageMagick-6/policy.xml /etc/ImageMagick-7/policy.xml; do \
        if [ -f "$f" ]; then \
            sed -i 's@rights="none" pattern="HEIC"@rights="read|write" pattern="HEIC"@' "$f"; \
            sed -i 's@rights="none" pattern="HEIF"@rights="read|write" pattern="HEIF"@' "$f"; \
        fi; \
    done

# Install Node.js 22 (required by laravel-vite-plugin ^2 and vite ^7)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && node -v \
    && npm -v

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

# Copy package files for npm
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci

# Copy the rest of the application
COPY . .

# Run composer scripts now that full app is present
RUN composer run-script post-autoload-dump || true

# Build frontend assets
RUN npm run build

# PHP-FPM config
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
