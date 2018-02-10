FROM registry.aliyuncs.com/syncxplus/php:7.1.14-nginx
LABEL maintainer="jibo@outlook.com"
COPY . /var/www
RUN cd /var/www \
    && composer config --global --auth github-oauth.github.com <token> \
    && composer install && composer clear-cache \
    && mv php.ini /usr/local/etc/php/php.ini \
    && mv web.php /var/www/vendor/bcosca/fatfree/lib/web.php
VOLUME ['/var/www/html/data']
RUN mv /var/www/default /etc/nginx/sites-available/default
