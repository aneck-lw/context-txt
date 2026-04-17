FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Replace default site config with ours (AllowOverride All + FollowSymLinks)
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy example site to web root
COPY example/ /var/www/html/

# Fix ownership
RUN chown -R www-data:www-data /var/www/html

# Railway injects PORT — update Apache to listen on it at startup
CMD ["/bin/bash", "-c", \
  "PORT=${PORT:-80}; \
   sed -i \"s/Listen 80/Listen $PORT/\" /etc/apache2/ports.conf; \
   sed -i \"s/*:80/*:$PORT/\" /etc/apache2/sites-available/000-default.conf; \
   apache2-foreground"]
