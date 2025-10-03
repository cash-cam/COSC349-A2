#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive


REPO_HTTPS="https://github.com/cash-cam/COSC349-A2.git"

# RDS connection, will need to be changed for redeployment
RDS_ENDPOINT="studentdata.c4eugvhp2zsa.us-east-1.rds.amazonaws.com"
DB_NAME="studentdata"

# App DB user (php use)
APP_DB_USER="webuser"
APP_DB_PASS="claca067"

# RDS master credentials
MASTER_DB_USER="webuser"
MASTER_DB_PASS="claca067"

# Control whether we create DB + user and import schema needed for when i was trying to update ec2's but already built db
PROVISION_DB="true"
# ====================

echo 'Acquire::ForceIPv4 "true";' | tee /etc/apt/apt.conf.d/99force-ipv4 >/dev/null
apt-get update -y
apt-get install -y apache2 php libapache2-mod-php php-mysql git unzip mysql-client

# Fresh clone (repo root will be /tmp/COSC349-A2 with that name)
rm -rf /tmp/COSC349-A2
git clone "$REPO_HTTPS" /tmp/COSC349-A2

# Deploy ONLY the API subtree
rsync -a --delete --exclude='.git/' /tmp/COSC349-A2/api/ /var/www/html/
chown -R www-data:www-data /var/www/html

# --- Apache vhost for API ---
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

  # DB env exposed to PHP
  SetEnv DB_HOST ${RDS_ENDPOINT}
  SetEnv DB_NAME ${DB_NAME}
  SetEnv DB_USER ${APP_DB_USER}
  SetEnv DB_PASS ${APP_DB_PASS}

  ErrorLog \${APACHE_LOG_DIR}/api-error.log
  CustomLog \${APACHE_LOG_DIR}/api-access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite api.conf >/dev/null
systemctl reload apache2

# --- Optional DB bootstrap (idempotent) ---
if [ "\${PROVISION_DB}" = "true" ]; then
  # Create DB + app user 
  mysql -h "\${RDS_ENDPOINT}" -u "\${MASTER_DB_USER}" "-p\${MASTER_DB_PASS}" \
    -e "CREATE DATABASE IF NOT EXISTS \\\`\${DB_NAME}\\\`;
        CREATE USER IF NOT EXISTS '\${APP_DB_USER}'@'%' IDENTIFIED BY '\${APP_DB_PASS}';
        GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON \\\`\${DB_NAME}\\\`.* TO '\${APP_DB_USER}'@'%';
        FLUSH PRIVILEGES;"

  # Import schema if present
  if [ -f /tmp/COSC349-A2/db/schema.sql ]; then
    mysql -h "\${RDS_ENDPOINT}" -u "\${APP_DB_USER}" "-p\${APP_DB_PASS}" "\${DB_NAME}" < /tmp/COSC349-A2/db/schema.sql || true
  fi
fi

# Health Endpoint
cat >/var/www/html/public/health.php <<'PHP'
<?php
header('Content-Type: application/json');
try {
  $pdo = new PDO(
    "mysql:host=".getenv('DB_HOST').";dbname=".getenv('DB_NAME').";charset=utf8mb4",
    getenv('DB_USER'), getenv('DB_PASS'),
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
  $pdo->query("SELECT 1");
  echo json_encode(["php"=>"ok","db"=>"ok"]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["php"=>"ok","db"=>"fail","error"=>$e->getMessage()]);
}
PHP

# --- S3 backup prerequisites ---
apt-get install -y awscli

AWS_REGION="$(curl -s http://169.254.169.254/latest/dynamic/instance-identity/document | grep region | awk -F\" '{print $4}')"
S3_BUCKET="cosc349-a2-db-backups"     # Will need to be changed for redistribution
S3_PREFIX="rds-dumps"                    # optional folder prefix in bucket

# Backup script
cat >/usr/local/bin/db-backup.sh <<'EOF'
#!/usr/bin/env bash
set -euo pipefail

DB_HOST="${DB_HOST}"
DB_NAME="${DB_NAME}"
DB_USER="${DB_USER}"
DB_PASS="${DB_PASS}"
S3_BUCKET="cosc349-a2-db-backups"      # <-- keep in sync with user-data
S3_PREFIX="rds-dumps"

STAMP="$(date -u +%Y%m%d-%H%M%S)"
OUT="/var/backups/${DB_NAME}-${STAMP}.sql.gz"

mkdir -p /var/backups
mysqldump -h "$DB_HOST" -u "$DB_USER" "-p${DB_PASS}" --single-transaction --routines --triggers "$DB_NAME" \
  | gzip -9 > "$OUT"

# Upload to S3: s3://bucket/prefix/dbname/2025/10/03/file.sql.gz
aws s3 cp "$OUT" "s3://${S3_BUCKET}/${S3_PREFIX}/${DB_NAME}/$(date -u +%Y/%m/%d)/$(basename "$OUT")"

# keep local backups tidy (e.g., last 3)
ls -1t /var/backups/${DB_NAME}-*.sql.gz | tail -n +4 | xargs -r rm -f
EOF
chmod +x /usr/local/bin/db-backup.sh

# Cron: run at 02:30 UTC nightly
( crontab -l 2>/dev/null; echo "30 2 * * * /usr/local/bin/db-backup.sh >>/var/log/db-backup.log 2>&1" ) | crontab -
