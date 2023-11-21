FROM php:8.2-fpm

# Argumentos definidos no docker-compose.yml
ARG user
ARG uid

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
  git \
  curl \
  gnupg \
  libaio1 \
  libpng-dev \
  libonig-dev \
  libxml2-dev \
  libzip-dev \
  zip \
  unzip \
  libicu-dev \
  supervisor

# Limpar cache
RUN apt-get purge -y && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar biblioteca intl
RUN docker-php-ext-configure intl && docker-php-ext-install intl && docker-php-ext-enable intl 

# Instalar extensões do PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets zip soap

# Instalar extensão Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Instalar redis
RUN pecl install -o -f redis \
  && rm -rf /tmp/pear \
  && docker-php-ext-enable redis

# Obter o Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para executar comandos do Composer e Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
  chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

# Configurações do Xdebug
COPY ./docker-compose/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Portas usadas pelo xdebug e php-fpm
EXPOSE 9003 9000

USER $user
