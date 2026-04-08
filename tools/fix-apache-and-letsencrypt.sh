#!/usr/bin/env bash
set -euo pipefail

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this script as root."
    exit 1
fi

EMAIL="${1:-admin@computer-wizard.com.au}"

cat <<'EOF' > /etc/apache2/sites-available/ai.computer-wizard.com.au.conf
# ai.computer-wizard.com.au
<VirtualHost *:80>
    ServerName ai.computer-wizard.com.au
    Redirect permanent / https://ai.computer-wizard.com.au/
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =ai.computer-wizard.com.au
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<VirtualHost *:443>
    ServerName ai.computer-wizard.com.au

    DocumentRoot /var/www/html/ai.computer-wizard.com.au

    ErrorLog ${APACHE_LOG_DIR}/ai-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-access.log combined

    <Directory /var/www/html/ai.computer-wizard.com.au>
        AllowOverride All
        Options +Indexes
        Require all granted
    </Directory>

    ProxyPreserveHost On
    ProxyRequests Off

    <Location /clawd/app/>
        AuthType Basic
        AuthName "Clawd"
        AuthUserFile /etc/apache2/.htpasswd-clawd
        Require valid-user

        ProxyPass http://127.0.0.1:18789/
        ProxyPassReverse http://127.0.0.1:18789/

        RequestHeader set X-Forwarded-Proto "https"
    </Location>

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
</VirtualHost>
EOF

cat <<'EOF' > /etc/apache2/sites-available/events.computer-wizard.com.au.conf
<VirtualHost *:80>
    ServerName events.computer-wizard.com.au

    DocumentRoot /var/www/html/events.computer-wizard.com.au/public

    <Directory /var/www/html/events.computer-wizard.com.au/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/\.well-known/acme-challenge/
    RewriteRule ^/(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

    ErrorLog ${APACHE_LOG_DIR}/events.computer-wizard.com.au-error.log
    CustomLog ${APACHE_LOG_DIR}/events.computer-wizard.com.au-access.log combined
</VirtualHost>
EOF

mkdir -p /var/www/letsencrypt/.well-known/acme-challenge
chown -R www-data:www-data /var/www/letsencrypt
chmod -R 755 /var/www/letsencrypt

a2ensite events.computer-wizard.com.au.conf
apachectl configtest
systemctl reload apache2

certbot certonly \
    --webroot \
    -w /var/www/letsencrypt \
    -d ai.computer-wizard.com.au \
    --agree-tos \
    -m "${EMAIL}" \
    --non-interactive

certbot certonly \
    --webroot \
    -w /var/www/letsencrypt \
    -d events.computer-wizard.com.au \
    --agree-tos \
    -m "${EMAIL}" \
    --non-interactive

cat <<'EOF' > /etc/apache2/sites-available/ai.computer-wizard.com.au.conf
# ai.computer-wizard.com.au
<VirtualHost *:80>
    ServerName ai.computer-wizard.com.au
    Redirect permanent / https://ai.computer-wizard.com.au/
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =ai.computer-wizard.com.au
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<VirtualHost *:443>
    ServerName ai.computer-wizard.com.au

    DocumentRoot /var/www/html/ai.computer-wizard.com.au

    ErrorLog ${APACHE_LOG_DIR}/ai-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-access.log combined

    <Directory /var/www/html/ai.computer-wizard.com.au>
        AllowOverride All
        Options +Indexes
        Require all granted
    </Directory>

    ProxyPreserveHost On
    ProxyRequests Off

    <Location /clawd/app/>
        AuthType Basic
        AuthName "Clawd"
        AuthUserFile /etc/apache2/.htpasswd-clawd
        Require valid-user

        ProxyPass http://127.0.0.1:18789/
        ProxyPassReverse http://127.0.0.1:18789/

        RequestHeader set X-Forwarded-Proto "https"
    </Location>

    SSLEngine on
    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile /etc/letsencrypt/live/ai.computer-wizard.com.au/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/ai.computer-wizard.com.au/privkey.pem
</VirtualHost>
EOF

cat <<'EOF' > /etc/apache2/sites-available/events.computer-wizard.com.au-le-ssl.conf
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName events.computer-wizard.com.au

    DocumentRoot /var/www/html/events.computer-wizard.com.au/public

    <Directory /var/www/html/events.computer-wizard.com.au/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/events.computer-wizard.com.au-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/events.computer-wizard.com.au-ssl-access.log combined

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/events.computer-wizard.com.au/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/events.computer-wizard.com.au/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
</IfModule>
EOF

a2ensite events.computer-wizard.com.au-le-ssl.conf
apachectl configtest
systemctl reload apache2

echo "Done."
echo "Certificates requested for:"
echo "  - ai.computer-wizard.com.au"
echo "  - events.computer-wizard.com.au"
