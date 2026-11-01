#!/bin/sh

# Garante permissões corretas no storage e bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Executa migrações pendentes do banco de dados
php /var/www/artisan migrate --force

# Limpa e recompila o cache de views do Blade
php /var/www/artisan view:clear --quiet
php /var/www/artisan config:clear --quiet

# Inicia php-fpm em background
php-fpm -D

# Inicia nginx em foreground
nginx -g "daemon off;"
