#!/bin/bash

mkdir -p ~/.composer7.3

docker run -it --rm --tty \
--user $(id -u):$(id -g)  \
-w /usr/src/app \
--volume $PWD:/usr/src/app  \
--volume ${COMPOSER_HOME:-$HOME/.composer7.3}:/.composer \
--volume /etc/hosts:/etc/hosts \
 'composer7.3' composer -v $1 $2 $3


