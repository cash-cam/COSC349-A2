#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

# Credintials/links 
REPO_HTTPS="https://github.com/cash-cam/COSC349-A2.git"
RDS_ENDPOINT="studentdata.c4eugvhp2zsa.us-east-1.rds.amazonaws.com"
DB_NAME="studentdata"
DB_USER="webuser"
DB_PASS="claca067"


echo 'Acquire::ForceIPv4 "true";' | tee /etc/apt/apt.conf.d/99force-ipv4 >/dev/null
apt-get update -y
apt-get install -y apache2 php libapache2-mod-php php-mysql git unzip mysql-client

#
rm -rf /tmp/COSC349-A2
git clone "$REPO_HTTPS" /tmp/COSC349-A2

# Ship API subtree only need to do this as had rsync issues with the .git files
rsync -a --delete --exclude='.git/' /tmp/COSC349-A2/api/ /var/www/html/
chown -R www-data:www-data /var/www/html

# vhost for API (serves /var/www/html/public)
cat >/etc/apache2/sites-available/api.conf <<EOF
<VirtualHost *:80>
  ServerName _
  DocumentRoot /var/www/html/public
  <Directory /var/www/html/public>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
  DirectoryIndex index.php

  SetEnv DB_HOST ${RDS_ENDPOINT}
  SetEnv DB_NAME ${DB_NAME}
  SetEnv DB_USER ${DB_USER}
  SetEnv DB_PASS ${DB_PASS}
  ErrorLog \${APACHE_LOG_DIR}/api-error.log
  CustomLog \${APACHE_LOG_DIR}/api-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite api.conf >/dev/null
systemctl reload apache2

# populate rds
if [ -f /tmp/COSC349-A2/db/schema.sql ]; then 
  mysql -h "${RDS_ENDPOINT}" -u "${DB_USER}" "-p${DB_PASS}" "${DB_NAME}" < /tmp/COSC349-A2/db/schema.sql || true
fi