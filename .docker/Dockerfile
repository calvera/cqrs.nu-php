FROM devilbox/php-fpm:7.4-work

RUN apt-get update \
 && apt-get install -y nginx \
 && ln -s /proc/1/fd/2 /docker.stderr \
 && ln -s /proc/1/fd/1 /docker.stdout \
 && mkdir -p /run/nginx

COPY nginx/default.conf /etc/nginx/conf.d/default.conf
