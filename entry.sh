
# From inside the container or in Dockerfile
chown -R www-data:www-data /var/www/html/pmb
chmod -R u+rwX /var/www/html/pmb
chmod -R 775 /var/www/html/pmb

apache2-foreground
