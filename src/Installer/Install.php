<?php

namespace Boots\Installer;

/**
 * Boots installer for composer.
 *
 * @package Boots
 * @subpackage Installer\Install
 * @version 2.0.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

use PhpParser\Error;
use Composer\Script\Event;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Composer\Installer\PackageEvent;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard as PhpPrinter;

/**
 * @package Boots
 * @subpackage Installer\Install
 * @version 2.0.0
 */
class Install
{
    /**
     * Hackish method to get the composer project root directory path.
     * @param  string $dir Start from this directory
     * @return string|null Root directory
     */
    protected static function getRootDirectory($dir)
    {
        $rootDir = null;
        do {
            $dir = dirname($dir);
            if(file_exists($dir . '/composer.json'))
            {
                $rootDir = $dir;
            }
        } while(is_null($rootDir) && $dir != '/');
        return $rootDir;
    }

    protected static function mountFile($path2file, $version = '')
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser;
        $prettyPrinter = new PhpPrinter;
        // resolve names
        $traverser->addVisitor(new NameResolver);
        // custom visitor
        $visitor = new NodeVisitor($version);
        $traverser->addVisitor($visitor);
        // contents
        $code = file_get_contents($path2file);
        // parse
        $stmts = $parser->parse($code);
        // traverse
        $stmts = $traverser->traverse($stmts);
        // pretty print
        $code = $prettyPrinter->prettyPrintFile($stmts);
        // save file
        file_put_contents($path2file, $code);
        // return fqcn
        return str_replace('\\', '\\\\', $visitor->getFqcn());
    }

    protected static function writeManifest($path2manifest, array $data)
    {
        file_put_contents($path2manifest, json_encode($data));
    }

    protected static function extension(PackageEvent $event, $package)
    {
        $type = $package->getType();
        if ($type != 'boots-extension') {
            return;
        }
        $composer = $event->getComposer();
        $name = $package->getName();
        $version = $package->getPrettyVersion();
        $version = str_replace('-', '.', $version);
        $path = $composer->getInstallationManager()->getInstallPath($package);
        $path2file = "{$path}/index.php";
        $path2manifest = "{$path}/boots.json";
        if (!is_file($path2manifest)) {
            $fqcn = static::mountFile($path2file, $version);
            static::writeManifest($path2manifest, [
                'class' => $fqcn,
                'version' => $version,
            ]);
        }
    }

    public static function installExtension(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        static::extension($event, $package);
    }

    public static function updateExtension(PackageEvent $event)
    {
        $package = $event->getOperation()->getInitialPackage();
        static::extension($event, $package);
    }

    public static function installBoots(Event $event)
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $name = $package->getName();
        $version = $package->getPrettyVersion();
        $version = str_replace('-', '.', $version);
        if (is_null($path = static::getRootDirectory(__DIR__))) {
            echo 'Could not locate the base directory.';
            exit;
        }
        $path2manifest = "{$path}/boots.json";
        if (is_file($path2manifest)) {
            $jsonContents = file_get_contents($path2manifest);
            $mArr = json_decode($jsonContents, true);
            if (array_key_exists('version', $mArr) && !empty($mArr['version'])) {
                return;
            }
        }
        $fqcnApi = static::mountFile("{$path}/src/Api.php", $version);
        $fqcnRepo = static::mountFile("{$path}/src/Repository.php", $version);
        static::writeManifest($path2manifest, [
            'version' => $version
        ]);
    }
}