#!/bin/sh
php-fpm7 &
sh /docker-entrypoint.sh "nginx" "-g" "daemon off;"
