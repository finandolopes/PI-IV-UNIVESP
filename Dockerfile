# =====================================================
# CONFINTER - Dockerfile
# Imagem personalizada para o container web
# Data: 06/09/2025
# =====================================================

FROM php:8.1-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli gd zip mbstring xml curl openssl

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configurar Apache
RUN a2enmod rewrite headers ssl

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos de configuração
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor porta 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]

# =====================================================
# FIM DO ARQUIVO Dockerfile
# =====================================================
