FROM alpine:3.16

LABEL Maintainer="Rodrigo Levinski <rodrigo@amplimed.com.br>" \
      Description="Lightweight container with Nginx 1.20 & PHP 8.1 based on Alpine Linux."

# Install packages and remove default server definition
RUN apk --no-cache add php81 php81-fpm nginx supervisor curl tzdata

RUN apk --no-cache add php81-opcache php81-mysqli php81-openssl php81-curl \
    php81-zlib php81-xml php81-phar php81-intl php81-dom php81-xmlreader php81-ctype php81-session php81-simplexml\
    php81-mbstring php81-gd php81-bcmath php81-ftp php8-xmlwriter libexecinfo php81-pdo php81-pdo_pgsql php81-tokenizer
    
ENV TZ America/Sao_Paulo

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN rm -rf /usr/bin/php

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php81 /usr/bin/php

# Configure nginx
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/fastcgi_params /etc/nginx/fastcgi_params

# Configure PHP-FPM
COPY .docker/fpm-pool.conf /etc/php81/php-fpm.d/www.conf
COPY .docker/custom-php.ini /etc/php81/conf.d/custom.ini

# Configure supervisord
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

#intall datadog tracer
# test install: php --ri=ddtrace
RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php && php datadog-setup.php --php-bin=all

# Comentar/Descomentar aqui pra instalar o composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

# Setup document root
RUN mkdir -p /var/www/html

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html && \
  chown -R nobody.nobody /run && \
  chown -R nobody.nobody /var/lib/nginx && \
  chown -R nobody.nobody /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

# Add application
WORKDIR /var/www/html

COPY --chown=nobody composer.json composer.json

#descomente o comando abaixo para rodar a instalação das bibliotecas do compose
RUN composer install --no-dev

#descomente o comando abaixo para copiar os arquivos do repo
COPY --chown=nobody . /var/www/html/

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]