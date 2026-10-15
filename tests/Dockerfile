ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends zlib1g-dev libzip-dev; \
    docker-php-ext-install zip pdo_mysql; \
    rm -rf /var/lib/apt/lists/*
WORKDIR /work
COPY src/ ./src/
COPY tests/ ./tests/
COPY nyaaxport.php nyaaxport_56.php nyaaxport_cloud.php ./
CMD ["php", "tests/regression.php"]
