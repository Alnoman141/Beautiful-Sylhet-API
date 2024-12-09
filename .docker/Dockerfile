FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    redis-server \
    supervisor \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring exif pcntl bcmath zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set file permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html

# Add crontab file to the cron directory
COPY .docker/scheduler/cronfile /etc/cron.d/cronfile
RUN chmod 0644 /etc/cron.d/cronfile && \
    crontab /etc/cron.d/cronfile

# Add supervisor configuration file
COPY .docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Expose port
EXPOSE 80

# Start PHP-FPM server
CMD ["php-fpm"]
