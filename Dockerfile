FROM node:latest

WORKDIR /app

COPY package.json /app
RUN npm install
CMD ["npm","start"]

FROM php:7.4-fpm-alpine

RUN apk update && apk add --no-cache $PHPIZE_DEPS \
   build-base shadow nano curl gcc git bash vim \
   php7 \
   php7-fpm \
   php7-common \
   php7-pdo \
   php7-pdo_mysql \
   php7-mysqli \
   php7-mcrypt \
   php7-mbstring \
   php7-xml \
   php7-openssl \
   php7-json \
   php7-phar \
   php7-zip \
   php7-gd \
   php7-dom \
   php7-session \
   php7-zlib \
   haveged

   # # Install extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql

   # Remove Cache
RUN rm -rf /var/cache/apk/*

RUN sh -c "wget https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 -O cloud_sql_proxy && chmod +x cloud_sql_proxy"

RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"


RUN cd /app && /usr/local/bin/composer install --no-dev  


RUN chown -R www-data: /app

CMD sh /app/docker/startup.sh
