#!/usr/bin/env bash

cd ../docker/ && docker-compose up -d wordpress && cd ../bin/ || echo 'Please make sure the docker containers "wordpress" and "db" are started'

DB_USER='root'
DB_PASS='root'
DB_HOST='demovox-db'

WP_CLI_CMD="docker run -it --volumes-from demovox-wordpress -v /dev/null:/etc/php/7.4/cli/conf.d/xdebug3.ini
                --network container:demovox-wordpress
                -e WORDPRESS_DB_HOST=$DB_HOST -e WORDPRESS_DB_USER=$DB_USER -e WORDPRESS_DB_PASSWORD=$DB_PASS
                wordpress:cli"

chmod o+w ../languages/

 $WP_CLI_CMD i18n make-pot /var/www/html/wp-content/plugins/demovox/\
  --domain='demovox' /var/www/html/wp-content/plugins/demovox/languages/demovox.po\
  --exclude=/buildWpOrg/,/tests/\
  --headers='{"Language": "en_UK", "Language-Team": "SP Schweiz <info@spschweiz.ch>", "Last-Translator": "SP Schweiz <info@spschweiz.ch>", "Report-Msgid-Bugs-To": "https://github.com/spschweiz/demovox"}'

 $WP_CLI_CMD i18n make-pot /var/www/html/wp-content/plugins/demovox/\
  --domain='demovox.admin' /var/www/html/wp-content/plugins/demovox/languages/demovox.admin.po\
  --subtract=/var/www/html/wp-content/plugins/demovox/languages/demovox.po\
  --headers='{"Language": "en_UK", "Language-Team": "SP Schweiz <info@spschweiz.ch>", "Last-Translator": "SP Schweiz <info@spschweiz.ch>", "Report-Msgid-Bugs-To": "https://github.com/spschweiz/demovox"}'
