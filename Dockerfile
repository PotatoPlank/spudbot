FROM php:8.1-cli

ENV DOCKER 1

RUN groupadd -r spudbot && useradd --no-log-init -r -g spudbot spudbot

RUN apt-get update && apt-get install -y \
    libzip-dev \
    git  \
    curl \
    zip \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY src /usr/src/spudbot/src
COPY composer.json composer.lock spudbot.php /usr/src/spudbot/
WORKDIR /usr/src/spudbot

RUN composer install --no-dev

USER spudbot

ENTRYPOINT ["php", "./spudbot.php" ]