# Use the official PHP 7.4 with Apache image
FROM php:7.4-apache

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libbz2-dev libicu-dev libxml2-dev libxslt1-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        bz2 \
        intl \
        soap \
        sockets \
        xsl \
        zip \
        session \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and harden Apache
RUN a2enmod rewrite \
    && echo "ServerTokens Prod\nServerSignature Off" >> /etc/apache2/conf-enabled/security.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP config
COPY php.ini /usr/local/etc/php/php.ini

# Copy PMB source code
COPY --chown=www-data:www-data . /var/www/html/pmb

# Set permissions
RUN chmod -R 755 /var/www/html \
    && chmod g+s /var/www/html \
    && chmod o-rwx /var/www/html

# Create PHP session dir
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chmod 770 /var/lib/php/sessions

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]