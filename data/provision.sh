#!/bin/bash

WORKSPACE=/var/www
ENVIRONMENT=development
PHP_INI=/etc/php5/fpm/php.ini
MYSQL_ROOT_PASS=password

if ! hash apt-get 2>/dev/null; then
    echo "Unsupported OS; unable to update."
    exit 1
fi

# update
sudo apt-get update
sudo apt-get -y upgrade

# change profile start directory
grep -Fq "$WORKSPACE" /home/vagrant/.profile
if [ "0" != "$?" ]; then
    echo "" >> /home/vagrant/.profile
    echo "cd $WORKSPACE" >> /home/vagrant/.profile
    echo "" >> /home/vagrant/.profile
fi

if ! hash ntpd 2>/dev/null; then
    # install ntp
    sudo apt-get install -y ntp
fi
if hash ntpd 2>/dev/null; then
    # set ntp source
    sudo service ntp stop
    sudo ntpdate -s time.nist.gov
    sudo service ntp start
fi

if ! hash php 2>/dev/null; then
    # install php
    sudo apt-get install -y php5-fpm
    sudo apt-get install -y php5-common
    sudo apt-get install -y php5-dev
    sudo apt-get install -y php5-cli
    sudo apt-get install -y php5
    sudo apt-get install -y php5-mcrypt
    sudo apt-get install -y php5-mysql
    sudo apt-get install -y php5-intl
    sudo apt-get install -y php-gettext
    sudo apt-get install -y php5-gmp
    sudo apt-get install -y php5-curl
    # sudo apt-get install -y php5-memcache
    # sudo apt-get install -y php5-memcached
fi
if [ -f "$PHP_INI" ]; then
    # set php defaults
    sudo sed -i "s/;*\s*cgi\.fix_pathinfo\s*=\s*1/cgi.fix_pathinfo = 0/" "$PHP_INI"
    # sudo sed -i "s/upload_max_filesize\s*=\s*[0-9]*M/upload_max_filesize = 500M/" "$PHP_INI"
    # sudo sed -i "s/post_max_size\s*=\s*[0-9]*M/post_max_size = 500M/" "$PHP_INI"
    # sudo sed -i "s/memory_limit\s*=\s*[0-9]*M/memory_limit = 600M/" "$PHP_INI"
    sudo sed -i "s/;*\s*date\.timezone\s*=\s*[^;]*/date\.timezone = UTC/" "$PHP_INI"
    sudo service php5-fpm restart
fi

if ! hash mysqld 2>/dev/null; then
    # install mysql
    echo "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASS" | sudo debconf-set-selections
    echo "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASS" | sudo debconf-set-selections
    sudo apt-get install -y mysql-server
fi
if hash mysqld 2>/dev/null; then
    # secure mysql
    mysql -u root -p$MYSQL_ROOT_PASS -e "DELETE FROM mysql.user WHERE User='';"
    mysql -u root -p$MYSQL_ROOT_PASS -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
    mysql -u root -p$MYSQL_ROOT_PASS -e "DROP DATABASE IF EXISTS test;"
    mysql -u root -p$MYSQL_ROOT_PASS -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
    mysql -u root -p$MYSQL_ROOT_PASS -e "FLUSH PRIVILEGES;"
fi

if ! hash nginx 2>/dev/null; then
    # install
    sudo apt-get install -y nginx
fi
if hash nginx 2>/dev/null; then
    sudo -E cp $WORKSPACE/data/default-site /etc/nginx/sites-available/default
    sudo -E service nginx restart
fi

cd $WORKSPACE

# install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
composer install

# set up database
# sudo mysql -h "localhost" -u "root" "-p${MYSQL_ROOT_PASS}" < "/var/www/data/01-create-database.sql"
