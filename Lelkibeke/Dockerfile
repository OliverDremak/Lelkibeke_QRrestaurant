FROM php:8.4

RUN apt-get update -y && apt-get install -y openssl zip unzip git libonig-dev
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo mbstring
WORKDIR /app
COPY . /app
RUN composer install
RUN docker-php-ext-install pcntl

CMD php artisan serve --host=0.0.0.0 --port=8181 & php artisan reverb:start --host=0.0.0.0 --port=8080

EXPOSE 8181
