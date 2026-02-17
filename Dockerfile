FROM node:20-slim AS node_builder
WORKDIR /app
COPY . .
RUN npm ci && npm run build

FROM serversideup/php:8.2-fpm-nginx
WORKDIR /var/www/html

# Install additional PHP extensions if needed
USER root
RUN apt-get update && apt-get install -y \
    php8.2-intl \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix permissions
RUN chown -R webuser:webgroup /var/www/html

USER webuser

# Copy app files
COPY --chown=webuser:webgroup . /var/www/html
COPY --from=node_builder --chown=webuser:webgroup /app/public/build /var/www/html/public/build

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port (Koyeb uses 8000 by default or 80, serversideup listens on 8080 by default for non-root)
# We can set ENV variables to control this.
ENV PHP_OPCACHE_ENABLE=1

EXPOSE 8080
