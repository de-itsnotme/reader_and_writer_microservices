# Dockerfile
FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI (optional but handy)
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www

