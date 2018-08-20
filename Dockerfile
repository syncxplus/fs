FROM registry.aliyuncs.com/syncxplus/php:7.2.8
LABEL maintainer="jibo@outlook.com"
COPY . /var/www
RUN cd /var/www && apxs -cia mod_xsendfile.c \
    && mv site.conf /etc/apache2/sites-available/000-default.conf \
    && mv mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
RUN mv /var/www/php.ini /usr/local/etc/php/php.ini
RUN chown -R www-data:www-data /var/www
USER www-data
RUN cd /var/www \
    && composer install && composer clear-cache \
    && mv base.php web.php /var/www/vendor/bcosca/fatfree/lib/
VOLUME ['/var/www/html/data']
USER root
