ARG PHP_VERSION

FROM php:${PHP_VERSION}-apache

# Install MySQLi module, enable mod_rewrite & proxies
RUN apt-get update \
    && apt-get install -y default-libmysqlclient-dev \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite proxy proxy_http proxy_html headers

# Set proxy for phpMyAdmin
RUN sed -i "/<\/VirtualHost>/e echo '\
\n\
\tRedirect /phpmyadmin /phpmyadmin/\n\
\tProxyPass /phpmyadmin/ http://vztm-phpmyadmin:80/\n\
\tProxyPassReverse /phpmyadmin/ http://vztm-phpmyadmin:80/'" \
    /etc/apache2/sites-available/000-default.conf