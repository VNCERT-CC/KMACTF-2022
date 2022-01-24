FROM php:fpm-alpine
RUN apk add --no-cache tzdata
ENV TZ Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Add php extensions
RUN apk add autoconf gcc make automake g++ make libffi-dev openssl-dev

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN rm -rf /tmp/pear

RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/www.conf

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /usr/local/etc/php/php.ini /usr/local/etc/php/conf.d/*

WORKDIR /home/
