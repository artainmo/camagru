FROM php:latest

COPY . /var/www/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN cd var/www/Controller/utils && composer install

EXPOSE 8000

CMD ["php","-S","localhost:8000", "-t", "/var/www/Controller"]
