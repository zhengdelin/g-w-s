FROM alpine:3.14
RUN npm install
RUN npm install bootstrap@latest bootstrap-icons @popperjs/core --save-dev
RUN npm install vue@next --save-dev
RUN npm install --save-dev vue-loader@next

CMD [ "node" ]


FROM php:7.4-fpm-alpine
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y
RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev
RUN composer update
RUN chown -R www-data: /app

CMD sh /app/docker/startup.sh


