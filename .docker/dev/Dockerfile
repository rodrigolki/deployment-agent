FROM alpine:3.16

LABEL Maintainer="Rodrigo Levinski <rodrigo@amplimed.com.br>" \
      Description="Lightweight container with Nginx 1.20 & PHP 8.1 based on Alpine Linux FOR DEV PURPOSE."

# Install packages and remove default server definition
RUN apk --no-cache add php81 php81-fpm nginx supervisor curl tzdata

RUN apk --no-cache add php81-opcache php81-mysqli php81-openssl php81-curl \
    php81-zlib php81-xml php81-phar php81-intl php81-dom php81-xmlreader php81-ctype php81-session php81-simplexml\
    php81-mbstring php81-gd php81-bcmath php81-ftp php8-xmlwriter libexecinfo php81-pdo php81-pdo_mysql php81-tokenizer\
    php81-pecl-xdebug

ENV TZ America/Sao_Paulo

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN rm -rf /usr/bin/php

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php81 /usr/bin/php

# Configure nginx
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/fastcgi_params /etc/nginx/fastcgi_params

# Configure PHP-FPM
COPY .docker/dev/fpm-pool.conf /etc/php81/php-fpm.d/www.conf
COPY .docker/custom-php.ini /etc/php81/conf.d/custom.ini

# Configure supervisord
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

#intall datadog tracer
# test install: php --ri=ddtrace
RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php && php datadog-setup.php --php-bin=all

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

# Setup document root
RUN mkdir -p /var/www/html
RUN mkdir -p /tmp/fonts

#configura o debugger pra funcionar no xdebug
RUN echo "zend_extension=/usr/lib/php81/modules/xdebug.so" > /etc/php81/conf.d/50_xdebug.ini \
    && echo "xdebug.mode=debug" >> /etc/php81/conf.d/50_xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /etc/php81/conf.d/50_xdebug.ini \
    && echo "xdebug.idekey=VSCODE" >> /etc/php81/conf.d/50_xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /etc/php81/conf.d/50_xdebug.ini  \
    && echo "xdebug.client_host=host.docker.internal" >> /etc/php81/conf.d/50_xdebug.ini  \
    && echo "xdebug.remote_enable=1" >> /etc/php81/conf.d/50_xdebug.ini  \
    && echo "xdebug.client_port=9008" >> /etc/php81/conf.d/50_xdebug.ini   

# Add application
WORKDIR /var/www/html

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]