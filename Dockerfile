FROM php:8.2-apache

# Instalar la extensión PDO MySQL que requiere tu función getDB()
RUN docker-php-ext-install pdo pdo_mysql

# Copiar todos tus archivos PHP al directorio del servidor Apache
COPY . /var/www/html/

# Exponer el puerto 80 estándar para la API
EXPOSE 80
