FROM registry.aliyuncs.com/syncxplus/php:7.1.13
LABEL maintainer="jibo@outlook.com"
ADD . /var/www/html
RUN composer install && composer clear-cache
COPY docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
COPY php.ini /usr/local/etc/php/php.ini
COPY base.php vendor/bcosca/fatfree/lib/base.php
COPY web.php vendor/bcosca/fatfree/lib/web.php
VOLUME ['/var/www/html/data']
