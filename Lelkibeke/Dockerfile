# Stage 1: Build a vendor folder using composer
ARG ver=8.2
FROM php:${ver}-cli AS build

# Állítsuk be a munkakönyvtárat
WORKDIR /app

# Másoljuk be a Composer-t a hivatalos composer image-ből
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Másoljuk be a composer fájlokat (és ha van, a composer.lock-ot is)
COPY composer.json composer.lock ./

# Telepítjük a rendszerfüggőségeket és a composer függőségeket
RUN set -ex; \
    apt-get update; \
    apt-get install -y git zip; \
    composer -n validate --strict; \
    composer -n install --no-scripts --ignore-platform-reqs --no-dev

# Stage 2: Final image with Apache (vagy fpm, ha inkább php-fpm-et használsz)
FROM php:${ver}-apache

# Telepítjük a szükséges PHP kiterjesztéseket és engedélyezzük az Apache mod rewrite-ot
RUN set -ex; \
    docker-php-ext-install pdo_mysql mysqli; \
    a2enmod rewrite

# Másoljuk át a vendor mappát az előző stage-ből
# (Így a composer install eredménye elérhető lesz a végleges konténerben)
COPY --from=build /app/vendor /var/www/vendor

# Másoljuk át a teljes alkalmazás forráskódját a konténerbe
# Győződj meg róla, hogy a build context tartalmazza az összes szükséges fájlt!
COPY . /var/www

# Ha szükséges, állítsd be a munkakönyvtárat (például a Laravel esetében)
WORKDIR /var/www

# Exponáljuk a megfelelő portokat
EXPOSE 80
