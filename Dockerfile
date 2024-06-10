FROM registry.valeur-et-capital.intra/commun/php:7.4-apache-buster as composer

#dépendances Git
RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev zip unzip git


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


#CLEANING
RUN apt-get autoclean && apt-get autoremove && rm -rf /var/lib/apt/lists/*
ARG UID=1000
RUN usermod -u ${UID} www-data

