# Use an official PHP runtime as a parent image
FROM php:8.1-fpm

# Install necessary PHP extensions and PostgreSQL support
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer (PHP package manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the project files into the container
COPY . .

# Install any PHP dependencies via Composer
RUN composer install

# Expose port 80 for the web server
EXPOSE 80

# Start the PHP built-in web server
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
