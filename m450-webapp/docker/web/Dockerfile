FROM php:8.3-apache-bookworm

EXPOSE 8020

RUN apt-get update && \
	apt-get dist-upgrade -y && \
	apt-get install --no-install-recommends -y \ 
	 	libsqlite3-dev \
		sqlite3 \
		unzip \
		locales
RUN	docker-php-ext-install -j$(nproc) opcache
RUN pecl install xdebug \
	&& docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/php.ini

# Setup composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php  --install-dir=/usr/local/bin --filename=composer && \
	php -r "unlink('composer-setup.php');"

# configure apache http server
RUN a2enmod rewrite actions

# Set the locale
RUN echo "de_CH.UTF-8 UTF-8" > /etc/locale.gen && locale-gen
ENV LANG de_CH.UTF-8
ENV LANGUAGE de_CH:en
ENV LC_ALL de_CH.UTF-8
RUN cp /usr/share/zoneinfo/Europe/Zurich /etc/localtime
WORKDIR /var/www/html

CMD composer install && apache2-foreground
