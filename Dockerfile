FROM chaplean/php:7.1
MAINTAINER Tom - Chaplean <tom@chaplean.coop>

ENV COMPOSER_HOME=/root/.composer

# Workdir
VOLUME /var/www/symfony
WORKDIR /var/www/symfony/
