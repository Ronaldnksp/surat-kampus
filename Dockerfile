FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Completely remove event MPM, keep only prefork
RUN a2dismod mpm_event 2>/dev/null; \
    rm -f /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load 2>/dev/null; \
    rm -f /usr/lib/apache2/modules/mod_mpm_event.so 2>/dev/null; \
    a2enmod mpm_prefork; \
    a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/uploads

# Allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
