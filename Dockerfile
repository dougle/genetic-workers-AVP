# syntax=docker/dockerfile:1.0.0-experimental

#
# Note - you'll need to do: export DOCKER_BUILDKIT=1
# and: docker build --ssh ...

#
# PHP Dependency install stage
#
FROM composer-xq:latest as phpdep

COPY application/database/ database/

COPY application/composer.json composer.json
COPY application/composer.lock composer.lock

# Install PHP dependencies in 'vendor'
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

#
# Final image build stage
#
FROM alpine:latest as final

ADD application /app/application
ADD application/.env.example /app/application/.env
COPY --from=phpdep /app/vendor/ /app/application/vendor/
ADD entrypoint.sh /entrypoint.sh

RUN \
	apk update && \
	apk upgrade && \
	apk add \
		php7 php7-fpm php7-pdo_sqlite php7-opcache \
		php7-json php7-phar && \
	cd /app/application && \
	sed -i 's/;daemonize = yes/daemonize = no/g' /etc/php7/php-fpm.conf && \
	echo "php_admin_value[memory_limit] = 1024M" >> /etc/php7/php-fpm.d/www.conf && \
	apk del --purge curl wget

CMD ["sh", "/entrypoint.sh"]
