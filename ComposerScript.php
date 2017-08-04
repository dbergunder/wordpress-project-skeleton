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
        // Symlink the WP vendor into /wp
        self::symlinkWP();

        // Perform Install/Update
        self::installWP($event->getIO());

        // Install WP Plugins
        $extras = $event->getComposer()->getPackage()->getExtra();
        self::installPlugins($extras['wordpress-plugins']);
    }

    /**
     * @param Event $event
     */
    public static function themeSymlink(Event $event)
    {
        // Symlink the themes folder into the /wp/wp-content/themes folder
        self::symlinkThemes();
    }

    /**
     * @param IOInterface $io
     */
    private static function installWP(IOInterface $io)
    {
        if (file_exists('wp-config.php')) {
            // Link existing wp-config.php file from dist
            exec("ln -s wp-config wp/wp-config.php");
        } else {
            // Install fresh copy and generate file
            $database = $io->ask("What is the database name?", "wordpress");
            $user = $io->ask("What is the database user?", "root");
            $password = $io->askAndHideAnswer("What is the database password?");

            exec("vendor/bin/wp config create --dbname=$database --dbuser=$user --dbpass=$password");

            $site = $io->ask("What is the site URL?");
            $title = $io->ask("What is the site Title?");
            $admin = $io->ask("What is the admin user name?");
            $email = $io->ask("What is the admin email address?");

            exec("vendor/bin/wp core install --path=./wp --url=$site --title=$title --admin_user=$admin --admin_email=$email");

            // Move and link wp-config.php file
            exec("mv wp/wp-config.php wp-config.php");
            exec("ln -s wp-config.php wp/wp-config.php");
        }
    }

    private static function symlinkWP()
    {
        // Symlink version from composer vendor
        exec("rm -rf wp && ln -s vendor/wordpress/wordpress wp");
        exec("rm -rf wp/wp-config-sample.php");

    }

    private static function symlinkThemes()
    {
        // Symlink all subfolders under themes directory to wordpress install theme directory
        exec("find themes -maxdepth 1 -mindepth 1 -type d -exec ln -s ../../../'{}' wp/wp-content/themes/ \\;");
    }

    /**
     * @param array $plugins
     */
    private static function installPlugins($plugins)
    {
        if (empty($plugins)) return;

        foreach ($plugins as $pluginName => $pluginVersion) {
            $version = $pluginVersion == '*' ? '' : "--version=$pluginVersion";
            // Use wp-cli to download and install plugins
            exec("vendor/bin/wp plugin install $pluginName $version");
        }
    }
}