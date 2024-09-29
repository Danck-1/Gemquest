# Stage 1: Build stage
FROM php:8.1-fpm-alpine AS build

# Set working directory
WORKDIR /var/www/html

# Copy composer.lock and composer.json files
COPY composer.json ./

# Install dependencies
RUN apk --no-cache add git unzip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PHP dependencies via Composer
RUN composer install --no-dev --optimize-autoloader

# Copy the application code
COPY . .

# Expose port 80
EXPOSE 80

# Stage 2: Production stage
FROM nginx:alpine

# Set working directory
WORKDIR /var/www/html

# Copy files from the build stage to the production stage
COPY --from=build /var/www/html /var/www/html

# Copy Nginx configuration file
COPY ./nginx.conf /etc/nginx/nginx.conf

# Expose port 80
EXPOSE 80

# Start Nginx server
CMD ["nginx", "-g", "daemon off;"]
