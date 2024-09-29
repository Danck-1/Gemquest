# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Composer to allow superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Copy all project files to the container
COPY . .

# Check the files in the directory
RUN ls -la /var/www/html

# Install PHP dependencies via Composer
RUN composer install
