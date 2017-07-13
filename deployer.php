<?php

namespace Deployer;

require_once __DIR__ . 'vendor/deployer/deployer/recipe/common.php';

serverList(__DIR__ . '/servers.yml');

/** WordPress Recipe */

// WP shared dirs
set('shared_dirs', ['wp/wp-content/uploads']);

// WP shared files
set('shared_files', ['wp/.htaccess', 'wp/wp-config.php']);

// WP writable dirs
set('writable_dirs', ['wp/wp-content']);

set('clear_paths', ['wp/wp-config-sample.php']);

// WP CLI executable directories
set('bin_dir', 'vendor/bin');
set('wp', '{bin_dir}/wp');

// WP CLI install WP Core
task('install:wp', function () {
    run('{wp} core download');
});

// WP CLI install plugins
task('install:plugins', function () {
    foreach (get('plugins') as $plugin) {
        run('{wp} plugin install' . $plugin);
    }
});

// WP Symlink Theme
task('install:themes', function () {
    run('find themes -maxdepth 1 -mindepth 1 -type d -exec ln -s ../../../\'{}\' wp/wp-content/themes/ \;');
});

task('reload:php-fpm', function () {
    run('sudo servicectl php7-fpm restart');
});

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'install:wp',
    'deploy:clear_paths',
    'deploy:shared',
    'install:plugins',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');

// Display success message on completion
after('deploy', 'success');
after('deploy', 'reload:php-fpm');