FROM php:8.2-apache

# Extensões necessárias (mysqli para conexão com o banco)
RUN docker-php-ext-install mysqli \
    && a2enmod rewrite

# Composer (para instalar as dependências do composer.json)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress

COPY . .

# Apache já serve a partir de /var/www/html por padrão nesta imagem
EXPOSE 80
