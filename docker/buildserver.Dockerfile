FROM ubuntu:focal
WORKDIR /var/demovox

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Zurich
RUN ln -fs /usr/share/zoneinfo/Europe/Zurich /etc/localtime

RUN apt-get update \
    && apt-get install -y nodejs npm python ruby composer gettext php-xml
RUN npm install -g grunt-cli sass

# WP unit tests
RUN apt-get install -y php-mbstring php-mysql subversion mysql-client
#RUN apt-get install -y phpunit

# xdebug
RUN apt-get install -y php-dev php-pear && pecl install xdebug
RUN echo "export PHP_IDE_CONFIG=\"serverName=build\"" >> /root/.bashrc