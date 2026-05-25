FROM php:8.0-apache
# Installiamo l'estensione per far parlare PHP con il Database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli