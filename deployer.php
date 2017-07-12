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
set('wp', '{bin_dir}/wp --path=./wp');

// WP CLI install WP Core
task('install:wp', function () {

});

// WP CLI install plugins
task('install:plugins', function () {

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
    'deploy:clear_paths',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');

// Display success message on completion
after('deploy', 'success');
after('deploy', 'reload:php-fpm');