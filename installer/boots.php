<?php

namespace installer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class boots
{
    private static $path_boots = null;
    private static $path_vendor = null;
    private static $path_extend = null;

    private static $remove_vendor = false;

    public static function postPackageInstallUpdate(PackageEvent $event)
    {
        $composer = $event->getComposer();

        $operation = $event->getOperation();

        $package = $operation->getPackage();

        $type = $package->getType();

        if($type != 'boots-extension')
        {
            return;
        }

        $name = $package->getName();

        $version = $package->getPrettyVersion();

        $path = $composer
                ->getInstallationManager()
                ->getInstallPath($package);

        $name_array = explode('/', $name);
        $package_name = ucwords($name_array[count($name_array)-1]);

        $class_name = 'Boots_' . $package_name;

        # http://goo.gl/JOAcZE
        $pattern = !@preg_match('/\pL/u', 'a')
        ? '/[^a-zA-Z0-9]/'
        : '/[^\p{L}\p{N}]/u';

        $new_class_name = preg_replace($pattern, '_', ($class_name . '_' . $version));

        $file = $path . DIRECTORY_SEPARATOR . 'api.php';

        $file_content = file_get_contents($file);

        $content = preg_replace('/class +Boots_.+\s*{/', 'class ' . $new_class_name . ' {', $file_content);

        file_put_contents($file, $content);

        $boots_file = $path . DIRECTORY_SEPARATOR . 'boots.json';
        $boots_json = '{
    "extension": "' . strtolower($package_name) . '",
    "class": "' . $new_class_name . '"
}';

        file_put_contents($boots_file, $boots_json);

        // set paths

        $goback = '';
        for($depth = 1; $depth <= count($name_array); $depth++)
        {
            $goback .= '..' . DIRECTORY_SEPARATOR;
        }

        if(!self::$path_vendor)
        {
            self::$path_vendor = realpath($path . DIRECTORY_SEPARATOR . $goback);
        }

        if(!self::$path_boots)
        {
            self::$path_boots = realpath(self::$path_vendor . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        }

        if(!self::$path_extend)
        {
            self::$path_extend = self::$path_boots . DIRECTORY_SEPARATOR . 'extend';
            if(!is_dir(self::$path_extend))
            {
                mkdir(self::$path_extend);
            }
        }

        $path_package = self::$path_extend . DIRECTORY_SEPARATOR;
        // Regex snippet from
        // http://php.net/manual/en/function.preg-replace.php#111695
        $re = '/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/';
        $path_package .= strtolower(preg_replace($re, '-$1', $package_name));

        if(is_dir($path_package))
        {
            # http://goo.gl/b2fOh8
            unlinkRecursive($path_package, true);
        }

        // copy package to extend directory

        # http://goo.gl/GSBB5g
        xcopy($path, $path_package);
    }

    public static function postCmdInstallUpdate(Event $event)
    {
        if(self::$remove_vendor && self::$path_vendor)
        {
            # http://goo.gl/b2fOh8
            unlinkRecursive(self::$path_vendor, true);
        }
    }

    public static function preCmdInstallUpdate(Event $event)
    {
        $io = $event->getIO();
        $message = "
_________________________________________________________________

                    GNU GENERAL PUBLIC LICENSE
                       Version 2, June 1991
              http://www.gnu.org/licenses/gpl-2.0.html

  Boots - The missing WordPress framework. http://wpboots.com
  Copyright (C) 2014  M. Kamal Khan

  Boots comes with ABSOLUTELY NO WARRANTY.
  This is free software, and you are welcome to redistribute it
  under certain conditions.

_________________________________________________________________


NOTE: All extension `class names` will be modified according to
their version specifics. However, this is not something you need
to worry about while you use them to develop your applications.

E.g. Boots_SomeExt 1.0.2 will be replaced with Boots_SomeExt_1_0_2

Enter to confirm installation (Y, n): ";
        if($io->askConfirmation($message, true))
        {
            $message = "Remove the vendor directory after installation (Y, n): ";
            if($io->askConfirmation($message, true))
            {
                self::$remove_vendor = true;
                return true;
            }
            return true;
        }
        exit;
    }
}


# http://goo.gl/GSBB5g

/**
 * Copy a file, or recursively copy a folder and its contents
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       string   $permissions New folder creation permissions
 * @return      bool     Returns true on success, false on failure
 */
function xcopy($source, $dest, $permissions = 0755)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source".DIRECTORY_SEPARATOR."$entry", "$dest".DIRECTORY_SEPARATOR."$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

# http://goo.gl/b2fOh8

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 */
function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
        }

        if (!@unlink($dir . DIRECTORY_SEPARATOR . $obj))
        {
            unlinkRecursive($dir.DIRECTORY_SEPARATOR.$obj, true);
        }
    }

    closedir($dh);

    if ($deleteRootToo)
    {
        @rmdir($dir);
    }

    return;
}





