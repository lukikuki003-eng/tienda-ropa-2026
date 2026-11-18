FROM php:8.2-apache

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache para que escuche en el puerto 80
RUN echo "Listen 80" > /etc/apache2/ports.conf

# Configurar el DocumentRoot
RUN echo '<VirtualHost *:80>\n    DocumentRoot /var/www/html\n    <Directory /var/www/html>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copiar archivos del proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Puerto
EXPOSE 80

# Iniciar Apache en primer plano (necesario para Railway)
CMD ["apache2-foreground"]
