FROM php:8.4-apache

# Install system dependencies and PHP extensions for Laravel with MySQL/PostgreSQL support.
# Dependencies in this stage are only required for building the final image.
# Node.js and asset building are handled in the Nginx stage, not here.
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev
RUN docker-php-ext-install pdo_mysql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY ./docker/php/xdebug.ini "${PHP_INI_DIR}/conf.d"

# copy apache files
RUN mkdir -p /etc/apache2/ssl
COPY ./docker/apache/* /etc/apache2/sites-available

# Set the working directory inside the container
RUN mkdir /var/www/ams

COPY . /var/www/ams

# Install Composer and dependencies
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN HASH=`curl -sS https://composer.github.io/installer.sig`
RUN php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

# install nodejs
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs

# install python
RUN apt-get update && \
    apt-get install -y \
    python3 \
    python3-pip \
    nano && \
    rm -rf /var/lib/apt/lists/*

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/ams

# enable sites
RUN a2ensite vhost && \
    a2dissite 000-default && \
    a2enmod rewrite && \
    a2enmod ssl && \
    service apache2 restart

# Switch to the non-privileged user to run the application
USER www-data
WORKDIR /var/www/ams

CMD ["apache2-foreground"]
