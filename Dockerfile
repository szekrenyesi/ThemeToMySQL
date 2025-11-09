# Base image
FROM ubuntu:22.04

# Set non-interactive mode
ENV DEBIAN_FRONTEND=noninteractive

# Update and install dependencies
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    git \
    apache2 \
    php7.4 \
    libapache2-mod-php7.4 \
    php7.4-mysql \
    php7.4-zip \
    php7.4-mbstring \
    php7.4-mcrypt \
    && apt-get clean

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Change Apache to listen on port 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Clone the repository
RUN git clone https://github.com/szekrenyesi/ThemeToMySQL.git .

# Create data folder with proper permissions
RUN mkdir -p /var/www/html/ThemeToMySQL/data && chmod -R 777 /var/www/html/ThemeToMySQL/data

# Rename config file
RUN mv conf/config.ini.default conf/config.ini || true

# Expose Apache port
EXPOSE 8080

# Start Apache in foreground
CMD ["apache2ctl", "-D", "FOREGROUND"]

