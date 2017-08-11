<?php

use Composer\Script\Event;
use Composer\Io\IOInterface;

/**
 * Static Composer Script for automating deployment
 */
class ComposerScript
{
    const WORDPRESS_VENDOR = 'wordpress' . DIRECTORY_SEPARATOR . 'wordpress';

    /**
     * @param Event $event
     */
    public static function wordPressInstall(Event $event)
    {
        $rootPath = dirname( __FILE__ );
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . self::WORDPRESS_VENDOR;

        // Symlink the WP vendor into /wp
        self::symlinkWP($rootPath, $vendorPath);

        // Perform Install/Update
        self::configSetup($event->getIO(), $rootPath, $vendorPath);

        // Install or update wordpress
        if (!self::isWPInstalled($rootPath)) {
            self::installWordPress($rootPath);
        }
    }

    /**
     * @param Event $event
     */
    public static function themeSymlink(Event $event)
    {
        $rootPath = dirname( __FILE__ );
        // Symlink the themes folder into the /wp/wp-content/themes folder
        self::symlinkThemes($rootPath);
    }

    /**
     * @param Event $event
     */
    public static function pluginInstall(Event $event)
    {
        $rootPath = dirname( __FILE__ );
        // Install WP Plugins
        $extras = $event->getComposer()->getPackage()->getExtra();
        self::installPlugins($extras['wordpress-plugins'], $rootPath);
    }

    /**
     * @param Event $event
     */
    public static function projectSetup(Event $event)
    {
        $rootPath = dirname( __FILE__ );
        $event->getIo()->write("Installing core WordPress to $rootPath");
    }

    /**
     * @param IOInterface $io
     * @param $rootPath
     * @param $vendorPath
     */
    private static function configSetup(IOInterface $io, $rootPath, $vendorPath)
    {
        // Create new wp-config and generate file
        if (!file_exists("$rootPath/wp-config.php")) {
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
            exec("mv $rootPath/wp/wp-config.php $rootPath/wp-config.php");
        }
        // Link existing wp-config.php file
        exec("ln -s $rootPath/wp-config.php $vendorPath/wp-config.php");
    }

    /**
     * @param $rootPath
     * @return bool
     */
    private static function isWPInstalled($rootPath)
    {
        // exit status 0 if installed, otherwise 1
        exec("$rootPath/vendor/bin/wp core is-installed", $output, $exitCode);

        return $exitCode == 0 ? true : false;
    }

    /**
     * @param $rootPath
     * @param $plugin
     * @return bool
     */
    private static function isPluginInstalled($rootPath, $plugin)
    {
        // exit status 0 if installed, otherwise 1
        exec("$rootPath/vendor/bin/wp plugin is-installed $plugin", $output, $exitCode);

        return $exitCode == 0 ? true : false;
    }

    /**
     * @param $rootPath
     */
    private static function installWordPress($rootPath)
    {
        exec("$rootPath/vendor/bin/wp core install");
    }

    /**
     * @param $rootPath
     * @param $vendorPath
     */
    private static function symlinkWP($rootPath, $vendorPath)
    {
        // Symlink version from composer vendor
        // Copy the uploads folder back to root
        if (!file_exists("$rootPath/uploads") && file_exists("$vendorPath/wp-content/uploads")) {
            exec("mkdir $rootPath/uploads");
            exec("cp $rootPath/wp/wp-content/uploads/* $rootPath/uploads");
        } elseif (file_exists("$vendorPath/wp-content/uploads")) {
            exec("cp $rootPath/wp/wp-content/uploads/* $rootPath/uploads");
        } elseif (!file_exists("$rootPath/uploads")) {
            exec("mkdir $rootPath/uploads");
        }

        // Remove old link to wordpress vendor and recreate wp folder
        // TODO: make wp folder setting from composer
        exec("rm -rf $rootPath/wp");
        exec("mkdir $rootPath/wp");

        // Copy the uploads folder back
        exec("ln -s $rootPath/uploads $vendorPath/wp-content/uploads");

        // Relink the vendor folder
        exec("ln -s $vendorPath/* wp");

        // Remove the config sample file
        exec("rm -rf $rootPath/wp/wp-config-sample.php");
    }

    /**
     * @param $rootPath
     */
    private static function symlinkThemes($rootPath)
    {
        // Symlink all subfolders under themes directory to wordpress install theme directory
        exec("find themes -maxdepth 1 -mindepth 1 -type d -exec ln -s $rootPath/'{}' $rootPath/wp/wp-content/themes/ \\;");
    }

    /**
     * @param array $plugins
     * @param $rootPath
     */
    private static function installPlugins($plugins, $rootPath)
    {
        if (empty($plugins)) return;

        foreach ($plugins as $pluginName => $pluginVersion) {
            $version = $pluginVersion == '*' ? '' : "--version=$pluginVersion";

            if (self::isPluginInstalled($rootPath, $pluginName)) {
                // Use wp-cli to update plugins
                exec("$rootPath/vendor/bin/wp plugin update $pluginName $version");
            } else {
                // Use wp-cli to download and install plugins
                exec("$rootPath/vendor/bin/wp plugin install $pluginName $version");
            }
        }
    }
}