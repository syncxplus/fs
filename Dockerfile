FROM registry.aliyuncs.com/syncxplus/php:7.1.13
LABEL maintainer="jibo@outlook.com"
COPY . /var/www
RUN cd /var/www \
    && composer config --global --auth github-oauth.github.com <token> \
    && composer install && composer clear-cache \
    && mv docker-php-entrypoint /usr/local/bin/docker-php-entrypoint \
    && mv php.ini /usr/local/etc/php/php.ini \
    && mv base.php /var/www/vendor/bcosca/fatfree/lib/base.php \
    && mv web.php /var/www/vendor/bcosca/fatfree/lib/web.php \
    && mv mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
VOLUME ['/var/www/html/data']
