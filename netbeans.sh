#!/bin/bash
ADDOPTION=$*
docker run -ti --rm  \
           -e DISPLAY=$DISPLAY \
           -v /tmp/.X11-unix:/tmp/.X11-unix:rw \
           -v `pwd`/.netbeans-docker:/home/developer/.netbeans \
           -v `pwd`:/workspace \
           ${ADDOPTION} \
           netbeans
