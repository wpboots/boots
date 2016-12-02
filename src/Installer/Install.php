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

    protected static function mountExtension($version, $path2file, $path2manifest)
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
        // save manifest
        $fqcn = str_replace('\\', '\\\\', $visitor->getFqcn());
        $manifest = "{\"class\":\"{$fqcn}\", \"version\":\"{$version}\"}";
        file_put_contents($path2manifest, $manifest);
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
        $path = $composer->getInstallationManager()->getInstallPath($package);
        $path2file = "{$path}/index.php";
        $path2manifest = "{$path}/boots.json";
        if (!is_file($path2manifest)) {
            static::mountExtension($version, $path2file, $path2manifest);
        }
    }

    public static function extensionInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        static::extension($event, $package);
    }

    public static function extensionUpdate(PackageEvent $event)
    {
        $package = $event->getOperation()->getInitialPackage();
        static::extension($event, $package);
    }

    public static function boots(Event $event)
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $name = $package->getName();
        $version = $package->getPrettyVersion();
        if (is_null($path = static::getRootDirectory(__DIR__))) {
            echo 'Could not locate the base directory.';
            exit;
        }
        dump(file_get_contents("{$path}/boots.json"));
        file_put_contents("{$path}/hello.txt", 'hello!');
        dump($name, $version, $path);
    }
}