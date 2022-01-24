#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" >/dev/null 2>&1 && pwd )"

HOST_NAME="proxy"
PUBLIC_PORT=1113
PUBLIC_PORT2=1114

docker run -d --restart=unless-stopped --name "ctf-${HOST_NAME}" --hostname "ctf-${HOST_NAME}" \
  -w /opt/app \
  -v "${DIR}/app:/opt/app:ro" \
  -v "/tmp/ctf-mounts:/tmp/ctf-mounts:rw" \
  -p ${PUBLIC_PORT}:9999 \
  -p ${PUBLIC_PORT2}:9998 \
  "alpine:latest" \
  ./app -connect-addr 103.28.172.12:1114
