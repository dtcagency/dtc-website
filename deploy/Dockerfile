FROM php:8.2-apache

# --- PHP extensions WordPress + Divi need ---
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
		libwebp-dev \
		libzip-dev \
		libicu-dev \
		libonig-dev \
		ghostscript \
		less \
	; \
	docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mysqli opcache zip; \
	rm -rf /var/lib/apt/lists/*

# mod_php only works with the prefork MPM; make sure no other MPM stays enabled.
RUN set -eux; \
	a2dismod mpm_event mpm_worker || true; \
	a2enmod mpm_prefork; \
	a2enmod rewrite headers expires remoteip; \
	a2enmod ssl proxy proxy_http substitute filter; \
	apache2ctl -t

# Reverse proxy voor de vacaturesite (same-origin iframe op /jobs-embed/)
COPY jobs-embed.conf /etc/apache2/conf-available/jobs-embed.conf
RUN a2enconf jobs-embed && apache2ctl -t

# --- PHP tuning ---
RUN set -eux; \
	{ \
		echo 'memory_limit = 512M'; \
		echo 'upload_max_filesize = 128M'; \
		echo 'post_max_size = 128M'; \
		echo 'max_execution_time = 300'; \
		echo 'max_input_vars = 5000'; \
		echo 'expose_php = Off'; \
	} > /usr/local/etc/php/conf.d/wordpress.ini; \
	{ \
		echo 'opcache.memory_consumption = 192'; \
		echo 'opcache.interned_strings_buffer = 16'; \
		echo 'opcache.max_accelerated_files = 10000'; \
		echo 'opcache.revalidate_freq = 2'; \
	} > /usr/local/etc/php/conf.d/opcache.ini

# --- Apache: allow .htaccess, trust Railway's proxy, quiet ServerName ---
RUN set -eux; \
	{ \
		echo 'ServerName localhost'; \
		echo 'ServerTokens Prod'; \
		echo 'ServerSignature Off'; \
		echo 'RemoteIPHeader X-Forwarded-For'; \
		echo '<Directory /var/www/html>'; \
		echo '    Options FollowSymLinks'; \
		echo '    AllowOverride All'; \
		echo '    Require all granted'; \
		echo '</Directory>'; \
	} > /etc/apache2/conf-available/z-dtc.conf; \
	a2enconf z-dtc

# --- Application ---
COPY app/ /var/www/html/
COPY wp-config.php /var/www/html/wp-config.php
COPY .htaccess /var/www/html/.htaccess
COPY tools/optimize-images.php /usr/local/bin/optimize-images.php
COPY docker-entrypoint.sh /usr/local/bin/dtc-entrypoint.sh

RUN chmod +x /usr/local/bin/dtc-entrypoint.sh \
	&& chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
ENTRYPOINT ["/usr/local/bin/dtc-entrypoint.sh"]
CMD ["apache2-foreground"]
