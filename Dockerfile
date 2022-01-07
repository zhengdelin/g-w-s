FROM node:latest
WORKDIR /app
COPY ./ /app
RUN npm install
RUN npm install bootstrap@latest bootstrap-icons @popperjs/core --save-dev
RUN npm install vue@next --save-dev
RUN npm install --save-dev vue-loader@next

CMD [ "node","start" ]


FROM php:7.4-fpm-alpine
WORKDIR /app
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apk update && apk upgrade -y
RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf


RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN /usr/local/bin/composer install --no-dev
RUN composer update
RUN chown -R www-data: /app

CMD sh /app/docker/startup.sh


