#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive


REPO_HTTPS="https://github.com/cash-cam/COSC349-A2.git"
# API's *private* IP or private DNS (no trailing slash) must be changed to match that of the EC2 private IP
API_BASE_URL="http://10.0.29.102"   


echo 'Acquire::ForceIPv4 "true";' | tee /etc/apt/apt.conf.d/99force-ipv4 >/dev/null
apt-get update -y
apt-get install -y apache2 php libapache2-mod-php php-mysql php-curl git unzip

rm -rf /tmp/COSC349-A2
git clone "$REPO_HTTPS" /tmp/COSC349-A2

# Deploy ONLY the UI subtree
rsync -a --delete --exclude='.git/' /tmp/COSC349-A2/ui/ /var/www/html/
chown -R www-data:www-data /var/www/html

# Apache vhost for UI
cat >/etc/apache2/sites-available/ui.conf <<EOF
<VirtualHost *:80>
  ServerName _
  DocumentRoot /var/www/html/www
  <Directory /var/www/html/www>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
  DirectoryIndex index.php
  SetEnv API_BASE_URL ${API_BASE_URL}
  ErrorLog \${APACHE_LOG_DIR}/ui-error.log
  CustomLog \${APACHE_LOG_DIR}/ui-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite ui.conf >/dev/null
systemctl reload apache2