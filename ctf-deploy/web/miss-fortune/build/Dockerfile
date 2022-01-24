FROM php:fpm-alpine
RUN apk add --no-cache tzdata
ENV TZ Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Add php extensions
RUN apk add autoconf gcc make automake g++ make libffi-dev openssl-dev

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql
RUN apk add --no-cache freetype libjpeg-turbo libpng freetype-dev libjpeg-turbo-dev libpng-dev \
  && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
  && docker-php-ext-install gd \
  && docker-php-ext-enable gd \
  && apk del --no-cache freetype-dev libjpeg-turbo-dev libpng-dev

RUN rm -rf /tmp/pear

RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/www.conf

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /usr/local/etc/php/php.ini /usr/local/etc/php/conf.d/*

RUN apk add --no-cache python3 && \
  python3 -m ensurepip && \
  pip3 install --no-cache --upgrade pip setuptools

RUN pip3 install --no-cache mysql-connector mysql-connector-python

WORKDIR /home/
