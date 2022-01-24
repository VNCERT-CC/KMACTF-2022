#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="ctf-mysql"
RUN_DIR="${DIR}/mysqld"
DATA_DIR="${DIR}/data"

mkdir -p "${DATA_DIR}" 2>/dev/null
chmod 777 "${DATA_DIR}" -R 2>/dev/null
mkdir -p "${RUN_DIR}" 2>/dev/null
chmod 777 "${RUN_DIR}" -R 2>/dev/null

docker network inspect "${HOST_NAME}_private" >/dev/null 2>&1
if [ ! $? -eq 0 ]; then
  docker network create "${HOST_NAME}_private"
fi

docker container inspect "${HOST_NAME}" >/dev/null 2>&1
if [ $? -eq 0 ]; then
  docker stop "${HOST_NAME}"
  docker rm "${HOST_NAME}"
fi

docker run -d --restart=unless-stopped --name "${HOST_NAME}" --hostname "${HOST_NAME}" \
  -v "${DATA_DIR}:/var/lib/mysql" \
  -v "${RUN_DIR}:/var/run/mysqld" \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -e MYSQL_DATABASE=default_db \
  -e MYSQL_USER=user \
  -e MYSQL_PASSWORD=user_password \
  --network="${HOST_NAME}_private" \
  mysql:latest
