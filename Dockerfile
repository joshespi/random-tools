FROM php:8.2-apache
RUN a2enmod rewrite
COPY src/ /var/www/html/
