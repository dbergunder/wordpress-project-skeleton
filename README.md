## *WIP* WordPress Composer skeleton with WP-CLI and Deployer
The intent of this project framework is to assist with releases and continuous integration for a wordpress site.
 - [WP-CLI](https://make.wordpress.org/cli/)
 - [Deployer](https://deployer.org)

### Install Steps
- Requires >= PHP 7.1, MySQL
- CP wp-config-sample.php to wp-config.php (make any necessary changes for your environment) _or_ allow composer scripts to generate wp-config.php file on new install
- Move servers.yml.dist to servers.yml for deployer script

1) Install composer
```bash
sh composer.sh
```

2) Install Project
```bash
# Option 1: Create a new project from this skeleton
php composer.phar create-project dbergunder/wordpress-project-skeleton ./your-project-name

# Option 2: Clone this repo and run composer install
php composer.phar install
```

3) Install wp
```
# Option 1: Symlink wordpress, install plugins, and link theme folders
php composer.phar symlink-wordpress-cmd
php composer.phar symlink-themes-cmd

# Option 2: Install remotely via deployer (requires shared folders and files, and database setup)
# Note: Recipe is untested.
php vendor/bin/dep deploy {target}
```

### Development Notes
Development work belongs under the ./themes folder and should be treated as the wp-content/themes directory, which will symlink after release.  Make sure to have a wp-config.php file on the environment server.

### Host Configurations
- https://codex.wordpress.org/htaccess
- https://codex.wordpress.org/Nginx
