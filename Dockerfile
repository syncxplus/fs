FROM registry.aliyuncs.com/syncxplus/php:7.2.7
LABEL maintainer="jibo@outlook.com"
COPY . /var/www
RUN cd /var/www && apxs -cia mod_xsendfile.c \
    && mv site.conf /etc/apache2/sites-available/000-default.conf \
    && mv mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
RUN mv /var/www/php.ini /usr/local/etc/php/php.ini
RUN chown -R www-data:www-data /var/www
USER www-data
RUN cd /var/www \
    && composer config --global --auth github-oauth.github.com <token> \
    && composer install && composer clear-cache \
    && mv base.php /var/www/vendor/bcosca/fatfree/lib/base.php \
    && mv web.php /var/www/vendor/bcosca/fatfree/lib/web.php
VOLUME ['/var/www/html/data']
USER root
