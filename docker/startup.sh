#!/bin/sh

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

php-fpm -D

gcloud sql instances describe g-w-s

sudo mkdir /cloudsql; sudo chmod 777 /cloudsql

./cloud_sql_proxy -dir=/cloudsql -instances=g-w-s-337502:asia-east1:g-w-s &

./cloud_sql_proxy -dir=/cloudsql &

mysql -u root -p -S /cloudsql/g-w-s-337502:asia-east1:g-w-s
echo 'azbxccdv123'
mysql use genshin

while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx

php artisan migrate