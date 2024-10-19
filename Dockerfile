# Use an official PHP image with Apache
FROM php:8.0-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy all project files to the container
COPY . /var/www/html

# Install necessary extensions including PostgreSQL, MySQL, and GD
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Expose port 80 for web traffic
EXPOSE 80

# Copy custom php.ini configuration
COPY php.ini /usr/local/etc/php/

# Start Apache server
CMD ["apache2-foreground"]
