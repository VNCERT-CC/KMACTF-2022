#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="bigint"
PUBLIC_PORT=1111

docker run -d --restart=unless-stopped --name "ctf-${HOST_NAME}" --hostname "ctf-${HOST_NAME}" \
  -v "${DIR}/app:/opt/app:ro" \
  -v "/tmp/ctf-mounts:/tmp/ctf-mounts:rw" \
  -v "${DIR}/proxy-cmd-alpine:/opt/proxy-cmd:ro" \
  -p ${PUBLIC_PORT}:9999 \
  "alpine:latest" \
  /opt/proxy-cmd -w /opt/app -- ./app
# install stdbuf
docker exec "ctf-${HOST_NAME}" apk add coreutils
