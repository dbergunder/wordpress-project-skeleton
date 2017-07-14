## *WIP* WordPress Composer skeleton with WP-CLI and Deployer
The intent of this project framework is to assist with releases and continuous integration for a wordpress site.
 - [WP-CLI](https://make.wordpress.org/cli/)
 - [Deployer](https://deployer.org)

### Install Steps
- Requires >= PHP 7.1, MySQL
- Move wp-install.sh.dist to wp-install.sh (make any necessary changes for your environment)
- Move servers.yml.dist to servers.yml for deployer script

1) Install composer
```bash
sh composer.sh
```
2) Install Project
```bash
# Option 1: Create a new project from this skeleton
php composer.phar create-project dbergunder/wordpress-project-skeleton ./your-project-name

# Option 2: Manually Run composer install for dependencies
php composer.phar install
```
3) Install wp
```
# Locally, first time setup
sh wp-install.sh

# *UNTESTED* Install remotely via deployer (requires shared folders and files, and database setup)
php vendor/bin/dep deploy {target}
```

### Development Work
Development work belongs under the ./themes folder and should be treated as the wp-content/themes directory, which will symlink after release.  This can also be manually performed via symlink.sh.

### Host Configurations
- https://codex.wordpress.org/htaccess
- https://codex.wordpress.org/Nginx
