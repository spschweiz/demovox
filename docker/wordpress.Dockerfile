FROM wordpress:latest
WORKDIR /var/www/html
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Forward mails to mailhog
RUN curl --location --output /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 && chmod +x /usr/local/bin/mhsendmail
RUN echo 'sendmail_path="/usr/local/bin/mhsendmail --smtp-addr=demovox-mailhog:1025 --from=no-reply@demovox.ch"' > /usr/local/etc/php/conf.d/mailhog.ini