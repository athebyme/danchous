# Используем официальный образ PHP с Apache
FROM php:8.1-apache # Выберите версию PHP, которая вам нужна (например, 7.4, 8.0, 8.1, 8.2)

# Установка необходимых PHP расширений
# Обновите список расширений в соответствии с потребностями вашего проекта
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

# Установка Composer (если он нужен для зависимостей PHP)
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем файлы проекта в рабочую директорию контейнера Apache
COPY . /var/www/html/

# Настройка прав доступа для папки uploads (если она используется)
# Веб-сервер Apache обычно работает от пользователя www-data
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads && chmod -R 775 /var/www/html/uploads

# Включаем mod_rewrite для ЧПУ (если нужно)
RUN a2enmod rewrite

# (Опционально) Копируем кастомный php.ini, если нужен
# COPY ./config/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# (Опционально) Копируем кастомную конфигурацию Apache vhost, если нужна
# COPY ./config/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Рабочая директория (для команд вроде `composer install` если вы их запускаете при сборке)
WORKDIR /var/www/html

# (Опционально) Если есть composer.json, можно установить зависимости при сборке образа
# RUN composer install --no-dev --optimize-autoloader

# Apache по умолчанию слушает порт 80 внутри контейнера
EXPOSE 80