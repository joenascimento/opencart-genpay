FROM php:7.2-fpm
RUN apt-get update && apt-get install -y libpng-dev zlib1g-dev libwebp-dev libxpm-dev libjpeg62-turbo-dev libfreetype6-dev libmcrypt-dev && apt-get clean
RUN pecl install mcrypt-1.0.3 && pecl install xdebug-2.6.0
RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir --enable-gd-native-ttf
RUN docker-php-ext-install gd mysqli pdo_mysql mbstring zip
RUN docker-php-ext-enable mcrypt zip gd xdebug
