FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-install \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . /var/www/html

# Criar diretório do banco de dados com permissões corretas
RUN mkdir -p /var/www/html/app/db && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/app/db

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Configurar permissões finais
RUN chown -R www-data:www-data /var/www/html/vendor

# Expor porta 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
