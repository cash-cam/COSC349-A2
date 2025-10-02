#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

# ===== EDIT ME (after API launches) =====
API_PRIVATE_IP="10.0.17.184"
# ========================================

echo 'Acquire::ForceIPv4 "true";' | tee /etc/apt/apt.conf.d/99force-ipv4 >/dev/null
apt-get update -y
apt-get install -y apache2
a2enmod proxy proxy_http headers rewrite >/dev/null

cat >/etc/apache2/sites-available/proxy.conf <<EOF
<VirtualHost *:80>
    ServerName _

    ProxyPreserveHost On
    RequestHeader set X-Forwarded-Proto "http"
    RequestHeader set X-Forwarded-For "%{REMOTE_ADDR}s"

    ProxyPass        / http://${API_PRIVATE_IP}:80/
    ProxyPassReverse / http://${API_PRIVATE_IP}:80/

    ErrorLog \${APACHE_LOG_DIR}/proxy-error.log
    CustomLog \${APACHE_LOG_DIR}/proxy-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite proxy.conf >/dev/null
systemctl reload apache2