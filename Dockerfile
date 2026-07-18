FROM php:8.2-apache

# Extensões necessárias (mysqli para conexão com o banco), unzip (necessário
# para o Composer conseguir baixar/instalar pacotes) e opcache (cache de
# bytecode do PHP — evita recompilar todos os arquivos a cada requisição).
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip libzip-dev \
    && docker-php-ext-install mysqli zip opcache \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php-opcache.ini /usr/local/etc/php/conf.d/zzz-opcache.ini

# Composer (para instalar as dependências do composer.json)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress

COPY . .

# Apache já serve a partir de /var/www/html por padrão nesta imagem
EXPOSE 80
