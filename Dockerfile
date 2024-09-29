# Use an official PHP image with Apache
FROM php:8.0-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy all project files to the container
COPY . /var/www/html

# Install necessary extensions, including PostgreSQL PDO driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Ensure the Apache mod_rewrite is enabled
RUN a2enmod rewrite

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
