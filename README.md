## WordPress composer skeleton with WP-CLI and Deployer
The intent of this project framework is to assist with releases and continuous integration for a wordpress site.

*WIP*

### Install Steps
- Requires >= PHP 7.1, MySQL
- Move wp-install.sh.dist to wp-install.sh (make any necessary changes for your environment)
- Move servers.yml.dist to servers.yml for deployer script
- Update wp-cli.yml destination folder to hold wp source files (wp default)

1) Install composer
```bash
sh composer.sh
```
2) Run composer install for dependencies
```bash
php composer.phar install
```
3) Install locally wp via wp-cli commands
```
sh wp-install.sh
```
3.b) *UNTESTED* Install remotely via deployer (requires shared folders and files, and database be setup)
```bash
php vendor/bin/dep deploy {target}
```

### Development Work
Theme work should go its own respective folder, which will symlink after release.

### Host Configurations
```
# Examples for setting up your host files

# .htaccess
# https://codex.wordpress.org/htaccess

# BEGIN WordPress
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
# END WordPress

# Apache VirtualHost
<VirtualHost *:*>
    ServerName localhost
    DocumentRoot "/var/www/html/example/wp"

    # BEGIN WordPress
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.php$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.php [L]
    </IfModule>
    # END WordPress
</VirtualHost>

# nginx
# https://codex.wordpress.org/Nginx
server {
        ## Your website name goes here.
        server_name domain.tld;
        ## Your only path reference.
        root /var/www/wordpress;
        ## This should be in your http block and if it is, it's not needed here.
        index index.php;

        location = /favicon.ico {
            log_not_found off;
            access_log off;
        }

        location = /robots.txt {
            allow all;
            log_not_found off;
            access_log off;
        }

        location / {
            # This is cool because no php is touched for static content.
            # include the "?$args" part so non-default permalinks doesn't break when using query string
            try_files $uri $uri/ /index.php?$args;
        }

        location ~ \.php$ {
            #NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini
            include fastcgi.conf;
            fastcgi_intercept_errors on;
            fastcgi_pass php;
            fastcgi_buffers 16 16k;
            fastcgi_buffer_size 32k;
        }

        location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
            expires max;
            log_not_found off;
        }
}
```