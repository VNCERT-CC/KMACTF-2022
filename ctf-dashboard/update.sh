#!/usr/bin/env bash
sudo rm -rf ./CTFd
curl -OL https://github.com/CTFd/CTFd/archive/master.zip
unzip master.zip
rm -f master.zip
mv CTFd-master CTFd
./patch.sh
