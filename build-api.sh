#!/bin/bash

apt-get update 
DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 php libapache2-mod-php php-mysql mysql-client
# symlink creation enabling the site
/usr/sbin/a2ensite test-website || true   # use explicit path just in case PATH is limited
# Copy test-website.conf into apaches sites-available directory '|| true' just means keep going if fails
cp /vagrant/test-website.conf /etc/apache2/sites-available/test-website.conf
a2ensite test-website
a2dissite 000-default
systemctl reload apache2

echo "API VM Running"

