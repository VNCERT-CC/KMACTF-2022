#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="miss-fortune"
RUN_DIR="${DIR}/mysqld"

if [[ "$(docker images -q "ctf-${HOST_NAME}:latest" 2> /dev/null)" == "" ]]; then
  docker build -t "ctf-${HOST_NAME}:latest" "${DIR}/build"
  if [ $? -eq 0 ]; then
      echo 'Build done'
  else
      echo 'Build failed'
      exit 1
  fi
fi

docker run -d --restart=unless-stopped --name "ctf-${HOST_NAME}" --hostname "ctf-${HOST_NAME}" \
  -v "${RUN_DIR}:/var/run/mysqld" \
  -v "${DIR}/conf/custom-php.ini:/usr/local/etc/php/conf.d/vinhjaxt-custom-php.ini:ro" \
  -v "${DIR}/conf/custom-php-fpm.conf:/usr/local/etc/php-fpm.d/www.conf:ro" \
  -v "${DIR}/../public_html/${HOST_NAME}:/home/public_html:rw" \
  -v "${DIR}/../data/${HOST_NAME}:/home/www-data:rw" \
  -v "${DIR}/../run/main/${HOST_NAME}:/home/run:rw" \
  -e HOSTNAME=localhost \
  "ctf-${HOST_NAME}:latest"

docker exec "ctf-${HOST_NAME}" sh -c 'python3 /home/public_html/run.py > /tmp/pyout.log 2>&1 &'
