## WordPress composer skeleton with WP-CLI and Deployer
The intent of this project framework is to support more easily support releases and continuous integration into a wordpress site.

*WIP*

### Install Steps
- Requires >= PHP 7.1, MySQL
- Move wp-install.sh.dist to wp-install.sh (make any necessary changes for your environment)
- Move servers.yml.dist to servers.yml for deployer script

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
3.b) Install remotely via deployer (requires shared folders and files, and database be setup)
```bash
php vendor/bin/dep deploy {target}
```