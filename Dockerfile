# First stage: Use composer image to handle dependencies
FROM composer:latest AS composer

# Second stage: The main PHP environment
FROM php:8.1-apache

# Copy Composer binary from composer stage
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Set Composer to allow superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Copy all project files to the container
COPY . .

# Check the files in the directory (optional, for debugging)
RUN ls -la /var/www/html

# Install PHP dependencies via Composer
RUN composer install

# Expose port 80
EXPOSE 80
