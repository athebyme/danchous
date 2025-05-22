# Используем официальный образ PHP с Apache
FROM php:8.1-apache

# Установка необходимых PHP расширений
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        zip \
        unzip \
        git \
        curl \
        vim \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli mbstring exif pcntl bcmath zip opcache

# Копируем файлы проекта в рабочую директорию контейнера Apache
COPY . /var/www/html/

# Создаем и настраиваем права доступа для папок uploads и assets
RUN mkdir -p /var/www/html/uploads/products && \
    mkdir -p /var/www/html/uploads/categories && \
    mkdir -p /var/www/html/uploads/brands && \
    mkdir -p /var/www/html/assets/images && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/assets && \
    chmod -R 775 /var/www/html/uploads && \
    chmod -R 755 /var/www/html/assets

# Включаем mod_rewrite для ЧПУ и .htaccess
RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod expires

# Настройка Apache для поддержки .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Настройка PHP
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory.ini && \
    echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/execution.ini

# Рабочая директория
WORKDIR /var/www/html

# Apache по умолчанию слушает порт 80 внутри контейнера
EXPOSE 80