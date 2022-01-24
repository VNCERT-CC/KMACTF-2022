#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="rce-me3"

if [[ "$(docker images -q "ctf-${HOST_NAME}:latest" 2> /dev/null)" == "" ]]; then
  docker build -t "ctf-${HOST_NAME}:latest" "${DIR}"
  if [ $? -eq 0 ]; then
      echo 'Build done'
  else
      echo 'Build failed'
      exit 1
  fi
fi

docker run -d --restart=unless-stopped --name "ctf-${HOST_NAME}" --hostname "ctf-${HOST_NAME}" \
  --network=host \
  -v "${DIR}/conf/custom-php.ini:/etc/php7/conf.d/vinhjaxt-custom-php.ini:ro" \
  -v "${DIR}/conf/custom-php-fpm.conf:/etc/php7/php-fpm.d/www.conf:ro" \
  -v "${DIR}/public_html:/home/public_html/${HOST_NAME}:ro" \
  -v "${DIR}/run:/home/run/${HOST_NAME}:rw" \
  \
  -v "${DIR}/nginx/conf.d:/etc/nginx/conf.d:ro" \
  -v "${DIR}/nginx/nginx.conf:/etc/nginx/nginx.conf:ro" \
  \
  -e HOSTNAME=localhost \
  --entrypoint="/vinhjaxt-entrypoint.sh" \
  -v "${DIR}/entrypoint.sh:/vinhjaxt-entrypoint.sh:ro" \
  "ctf-${HOST_NAME}:latest"
