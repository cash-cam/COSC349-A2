#!/bin/bash

apt-get update #resync package index files from sources
apt-get install -y apache2 # Apache proxy server
a2enmod proxy proxy_http headers # Enables three Apache modules
# Take everything from the below CONF and cat it to the file
cat >/etc/apache2/sites-available/proxy.conf <<'CONF'
<VirtualHost *:80>
ProxyPreserveHost On
ProxyPass        / http://192.168.56.12/
ProxyPassReverse / http://192.168.56.12/
ProxyRequests Off
</VirtualHost>
CONF
# Enable site config. server static files
# Disable apache defailt site so vhost is one that answers on port 80
a2ensite proxy
a2dissite 000-default
systemctl reloads apache2
echo "Frontend Ready. Visit http://127.0.0.1:8080/index.php "
echo "Make sure API & DB VM's are 'up' before going to above link."
echo "Can check VM's status with 'Vagrant status' in CLI"