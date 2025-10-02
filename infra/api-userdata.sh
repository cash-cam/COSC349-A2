#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

# ===== EDIT ME =====
REPO_HTTPS="https://github.com/cash-cam/COSC349-A2.git"
RDS_ENDPOINT="studentdata.c4eugvhp2zsa.us-east-1.rds.amazonaws.com"
DB_NAME="studentdata"
DB_USER="webuser"
DB_PASS="claca067"
# ===================

DOCROOT="/var/www/html/app/www"
STAGE="/tmp/app-clone"

# Prefer IPv4 for apt (avoids IPv6 unreachable noise)
echo 'Acquire::ForceIPv4 "true";' | tee /etc/apt/apt.conf.d/99force-ipv4 >/dev/null

apt-get update -y
apt-get install -y apache2 php libapache2-mod-php php-mysql git unzip mysql-client

# Fresh clone each build (simple + reliable)
rm -rf "$STAGE"
git clone "$REPO_HTTPS" "$STAGE"

# Copy repo to /var/www/html, ignore .git to avoid having the rsync issues
rsync -a --delete --exclude='.git/' "$STAGE/" /var/www/html/
chown -R www-data:www-data /var/www/html

# Write vhost pointing at the real app web root
cat >/etc/apache2/sites-available/app.conf <<EOF
<VirtualHost *:80>
    ServerName _
    DocumentRoot ${DOCROOT}

    <Directory ${DOCROOT}>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    DirectoryIndex dashboard.php index.php index.html

    # DB credentials exposed to PHP via getenv()
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

# Tiny health endpoint (PHP + DB check)
cat >"${DOCROOT}/health.php" <<'PHP'
<?php
$ok=["php"=>"ok"];
try {
  $dbh=new PDO(
    "mysql:host=".getenv('DB_HOST').";dbname=".getenv('DB_NAME').";charset=utf8mb4",
    getenv('DB_USER'), getenv('DB_PASS'),
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
  $dbh->query("SELECT 1");
  $ok["db"]="ok";
} catch (Throwable $e) {
  http_response_code(500);
  $ok["db"]="fail"; $ok["error"]=$e->getMessage();
}
header('Content-Type: application/json'); echo json_encode($ok);
PHP

# Try schema import if present
if [ -f "/var/www/html/app/db/schema.sql" ]; then
  mysql -h "${RDS_ENDPOINT}" -u "${DB_USER}" "-p${DB_PASS}" "${DB_NAME}" < /var/www/html/app/db/schema.sql || true
fi