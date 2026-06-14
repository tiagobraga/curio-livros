FROM php:8.4-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx

# Limpa cache do apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Copia o Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuração do diretório de trabalho
WORKDIR /var/www

# Copia arquivos do projeto
COPY . /var/www

# Ajusta permissões
RUN chown -R www-data:www-data /var/www

# Configuração do Nginx
COPY ./docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Script de inicialização
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8088

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
