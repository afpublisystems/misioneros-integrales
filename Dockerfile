FROM php:8.2-apache

# Instalar extensión pdo_mysql
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite de Apache (necesario para el router MVC)
RUN a2enmod rewrite

# Configurar AllowOverride para .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
