#!/usr/bin/env bash
set -euo pipefail

# ====== CONFIG (edit) ======
API_PRIVATE_IP="ip-10-0-17-184.ec2.internal"   # e.g. 10.0.17.184
SERVER_NAME="_"                     # or your domain, e.g. example.com
# ===========================

export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y apache2

a2enmod proxy proxy_http headers rewrite >/dev/null

# Write reverse-proxy vhost
cat >/etc/apache2/sites-available/proxy.conf <<EOF
<VirtualHost *:80>
    ServerName ${SERVER_NAME}

    ProxyPreserveHost On
    RequestHeader set X-Forwarded-Proto "http"
    RequestHeader set X-Forwarded-For "%{REMOTE_ADDR}s"

    ProxyPass        / http://${API_PRIVATE_IP}:80/
    ProxyPassReverse / http://${API_PRIVATE_IP}:80/

    # Optional simple health endpoint (served by backend too)
    <Location /health>
        Require all granted
    </Location>

    ErrorLog \${APACHE_LOG_DIR}/proxy-error.log
    CustomLog \${APACHE_LOG_DIR}/proxy-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null || true
a2ensite proxy.conf >/dev/null
systemctl reload apache2