<?php

namespace Deployer;

require_once __DIR__ . 'vendor/deployer/deployer/recipe/common.php';

serverList(__DIR__ . '/servers.yml');

/** WordPress Recipe */

// WP shared dirs
set('shared_dirs', ['wp-content/uploads']);

// WP shared files
set('shared_files', ['.htaccess', 'wp-config.php']);

// WP writable dirs
set('writable_dirs', ['wp-content']);

// WP CLI executable directories
set('bin_dir', 'vendor/bin');

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