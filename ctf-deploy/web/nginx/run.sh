#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="ctf-nginx"

docker run -d --restart=unless-stopped --name "${HOST_NAME}" --hostname "${HOST_NAME}" \
  -v "${DIR}/../public_html:/home/public_html:ro" \
  -v "${DIR}/../run/main:/home/run:ro" \
  -v "${DIR}/conf.d:/etc/nginx/conf.d:ro" \
  -v "${DIR}/nginx.conf:/etc/nginx/nginx.conf:ro" \
  -p 80:80 \
  nginx:alpine
