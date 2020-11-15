FROM php:7.4-apache

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y apache2 vim dialog apt-utils zip unzip gnupg2

## Install Imagick
RUN apt-get install -y ghostscript libmagickwand-dev --no-install-recommends && \
    pecl install imagick && docker-php-ext-enable imagick && \
    rm -rf /var/lib/apt/lists/*
## Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer
## Install Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update -qq && apt-get install -y yarn
## Allow Imagick to read&write PDF files
RUN sed -i 's/domain="coder" rights="none" pattern="PDF"/domain="coder" rights="read|write" pattern="PDF"/g' /etc/ImageMagick-6/policy.xml

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod proxy_fcgi ssl rewrite proxy proxy_balancer proxy_http proxy_ajp
RUN sed -i '$a\
ServerName localhost' /etc/apache2/apache2.conf

WORKDIR /var/www/html/

COPY pdfmanager.test.conf /etc/apache2/sites-available/
RUN a2ensite pdfmanager.test.conf

EXPOSE 80