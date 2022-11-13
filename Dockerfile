FROM php:8.1-fpm

RUN apt-get update && \
apt-get install -y git zip unzip && \
docker-php-ext-install pdo pdo_mysql && \
docker-php-ext-enable pdo_mysql

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD composer install -o && \
php bin/console --no-interaction doctrine:database:create --if-not-exists && \
php bin/console --no-interaction doctrine:migration:migrate --allow-no-migration && \
php bin/console --no-interaction doctrine:fixtures:load && \
chown 100:101 -R * && \
chmod g+w -R var && \
php-fpm 

EXPOSE 9000
