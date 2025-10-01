#!/bin/bash
apt-get update -y
if ! dpkg -s mysql-server >/dev/null 2>&1; then
  apt-get install -y mysql-server
fi

systemctl enable --now mysql
sudo sed -i 's/^\s*bind-address\s*=.*/bind-address = 192.168.56.13/' /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql


# DB setup
mysql -e "CREATE DATABASE IF NOT EXISTS studentdata;"
mysql -e "CREATE USER IF NOT EXISTS 'webuser'@'192.168.56.12' IDENTIFIED BY 'testing_password';"
mysql -e "GRANT ALL PRIVILEGES ON studentdata.* TO 'webuser'@'192.168.56.12';"
mysql -e "FLUSH PRIVILEGES;"

# Import schema only if present and not already loaded
if [ -f /vagrant/schema.sql ]; then
    if ! mysql -Nse "USE studentdata; SHOW TABLES LIKE 'papers';" | grep -q '^papers$'; then
    mysql studentdata < /vagrant/schema.sql
    fi
else
    echo "NOTE: /vagrant/schema.sql not found; skipping import" >&2
fi

echo "Datbase VM Running. Intialized and inserted with test data. "
echo "Database schema & test data can be altered or removed from the schema.sql "
