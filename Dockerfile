FROM php:latest

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql 
#Install pdo_pgsql to enable php to connect to postgresql

COPY . /var/www/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN cd var/www/Controller/utils && composer install

EXPOSE 8000

CMD ["php","-S","0.0.0.0:8000", "-t", "/var/www/Controller"] 
# Address 0.0.0.0 instead of localhost enables to accept connections from outside the docker container itself 
# (https://stackoverflow.com/questions/25591413/docker-with-php-built-in-server)
