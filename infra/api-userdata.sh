#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

REPO_HTTPS="https://github.com/cash-cam/COSC349-A2.git"
RDS_ENDPOINT="studentdata.c4eugvhp2zsa.us-east-1.rds.amazonaws.com"
DB_NAME="studentdata"
DB_USER="webuser"
DB_PASS="claca067"

apt-get update -y
apt-get install -y apache2 php libapache2-mod-php php-mysql git unzip

# fresh clone each boot is fine for the lab; or guard with [ -d ] like before
rm -rf /tmp/app-clone
git clone "${REPO_HTTPS}" /tmp/app-clone

rsync -a --delete --exclude='.git/' /tmp/app-clone/ /var/www/html/
chown -R www-data:www-data /var/www/html

cat >/etc/apache2/sites-available/app.conf <<EOF
<VirtualHost *:80>
    ServerName _
    DocumentRoot /var/www/html/www
    <Directory /var/www/html/www>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    DirectoryIndex dashboard.php index.php index.html
    SetEnv DB_HOST ${RDS_ENDPOINT}
    SetEnv DB_NAME ${DB_NAME}
    SetEnv DB_USER ${DB_USER}
    SetEnv DB_PASS ${DB_PASS}
    ErrorLog \${APACHE_LOG_DIR}/app-error.log
    CustomLog \${APACHE_LOG_DIR}/app-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite app.conf >/dev/null
systemctl reload apache2