FROM ubuntu:bionic
WORKDIR /var/demovox

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Zurich
RUN ln -fs /usr/share/zoneinfo/Europe/Zurich /etc/localtime

RUN apt-get update \
    && apt-get install -y nodejs npm python ruby composer gettext php-xml phpunit
RUN npm install -g grunt-cli sass