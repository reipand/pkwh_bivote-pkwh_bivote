FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli zip mbstring xml

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY docker/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/sites-available/multi-site.conf /etc/apache2/sites-available/multi-site.conf

# Enable multi-site configuration
RUN a2ensite multi-site.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose ports
EXPOSE 80 443

# Start Apache
CMD ["apache2-foreground"]
