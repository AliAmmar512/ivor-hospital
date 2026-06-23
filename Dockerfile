FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    gnupg2 \
    apt-transport-https \
    unixodbc-dev \
    gcc \
    g++ \
    make \
    autoconf \
    && rm -rf /var/lib/apt/lists/*

# Install Microsoft ODBC Driver 18 (Debian 12 / Bookworm)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | \
        gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg && \
    curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list \
        -o /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 unixodbc-dev && \
    rm -rf /var/lib/apt/lists/*

# Install PHP sqlsrv extensions (pinned versions for PHP 8.1)
RUN pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy app files
COPY ivor_hospital/ /var/www/html/ivor_hospital/

# Add Apache config to allow access and set directory options
RUN echo '<Directory /var/www/html/ivor_hospital>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/ivor_hospital.conf \
    && a2enconf ivor_hospital

RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80