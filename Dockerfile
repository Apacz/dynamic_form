FROM php:8.2-fpm

# Install required dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev unzip curl \
    && docker-php-ext-install intl

# Install Composer manually
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

CMD ["php-fpm"]
