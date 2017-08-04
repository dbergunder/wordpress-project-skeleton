<?php

use Composer\Script\Event;
use Composer\Io\IOInterface;

/**
 */
class ComposerScript
{
    /**
     * @param Event $event
     */
    public static function wordPressInstall(Event $event)
    {
        $path = dirname( __FILE__ );
        // Symlink the WP vendor into /wp
        self::symlinkWP($path);

        // Perform Install/Update
        self::configSetup($event->getIO(), $path);

        // Install WP Plugins
        $extras = $event->getComposer()->getPackage()->getExtra();
        self::installPlugins($extras['wordpress-plugins'], $path);
    }

    /**
     * @param Event $event
     */
    public static function themeSymlink(Event $event)
    {
        $path = dirname( __FILE__ );
        // Symlink the themes folder into the /wp/wp-content/themes folder
        self::symlinkThemes($path);
    }

    /**
     * @param IOInterface $io
     * @param $path
     */
    private static function configSetup(IOInterface $io, $path)
    {
        if (file_exists($path.'/wp-config.php')) {
            // Link existing wp-config.php file
            exec("ln -s wp-config.php $path/wp/wp-config.php");
        } else {
            // Create new wp-config and generate file
            $database = $io->ask("What is the database name?(wordpress)", "wordpress");
            $user = $io->ask("What is the database user?(root)", "root");
            $password = $io->askAndHideAnswer("What is the database password?");

            exec("vendor/bin/wp config create --dbname=$database --dbuser=$user --dbpass=$password");

            $site = $io->ask("What is the site URL?");
            $title = $io->ask("What is the site Title?");
            $admin = $io->ask("What is the admin user name?");
            $email = $io->ask("What is the admin email address?");

            exec("vendor/bin/wp core install --path=./wp --url=$site --title=$title --admin_user=$admin --admin_email=$email");

            // Move and link wp-config.php file
            exec("mv $path/wp/wp-config.php $path/wp-config.php");
            exec("ln -s $path/wp-config.php $path/wp/wp-config.php");
        }
    }

    /**
     * @param $path
     */
    private static function symlinkWP($path)
    {
        // Symlink version from composer vendor
        exec("rm -rf $path/wp");
        exec("mkdir $path/wp");
        exec("ln -s $path/vendor/wordpress/wordpress/* wp");
        exec("rm -rf $path/wp/wp-config-sample.php");
    }

    /**
     * @param $path
     */
    private static function symlinkThemes($path)
    {
        // Symlink all subfolders under themes directory to wordpress install theme directory
        exec("find themes -maxdepth 1 -mindepth 1 -type d -exec ln -s $path/'{}' wp/wp-content/themes/ \\;");
    }

    /**
     * @param array $plugins
     * @param $path
     */
    private static function installPlugins($plugins, $path)
    {
        if (empty($plugins)) return;

        foreach ($plugins as $pluginName => $pluginVersion) {
            $version = $pluginVersion == '*' ? '' : "--version=$pluginVersion";
            // Use wp-cli to download and install plugins
            exec("$path/vendor/bin/wp plugin install $pluginName $version");
        }
    }
}