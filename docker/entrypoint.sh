#!/bin/sh

# Inicia php-fpm em background
php-fpm -D

# Inicia nginx em foreground
nginx -g "daemon off;"
