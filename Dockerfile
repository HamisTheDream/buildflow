FROM node:20-slim AS node_builder
WORKDIR /app
COPY . .
RUN echo "Cache Bust 1" && npm ci && npm run build

FROM serversideup/php:8.2-fpm-nginx
WORKDIR /var/www/html

# Install additional PHP extensions if needed
# Extensions are pre-installed in serversideup image


# Fix permissions
# Fix permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data

# Copy app files
COPY --chown=www-data:www-data . /var/www/html
COPY --from=node_builder --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Link public storage so uploaded files are servable via /storage/*
RUN php artisan storage:link

# Expose port (Koyeb uses 8000 by default or 80, serversideup listens on 8080 by default for non-root)
# We can set ENV variables to control this.
ENV PHP_OPCACHE_ENABLE=1

EXPOSE 8080
