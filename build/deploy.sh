#!/usr/bin/env bash

docker login -u "$DOCKER_LOGIN" -p "$DOCKER_PASSWORD"
docker push 421p/mophpidy:latest
docker push 421p/mophpidy:arm32v7
docker push 421p/mophpidy:${APP_VERSION}
docker push 421p/mophpidy:${APP_VERSION}-arm32v7
