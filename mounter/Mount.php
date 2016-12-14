<?php

namespace Boots\Mounter;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Mounter\Mount
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

use PhpParser\Error;
use Composer\Script\Event;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Bhittani\PhpParser\AppendSuffixVisitor;

/**
 * @package Boots
 * @subpackage Mounter\Mount
 */
class Mount
{
    protected static function sanitize($str)
    {
        $re = '/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/';
        return strtolower(preg_replace($re, '.$1', str_replace(' ', '', lcfirst(ucwords(
            str_replace(['-', '_'], ' ', $str)
        )))));
    }

    protected static function mount($dir, $suffix, array $regexes = [])
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser;
        $prettyPrinter = new Standard;
        $traverser->addVisitor(new AppendSuffixVisitor($suffix, $regexes));
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = new \RegexIterator($files, '/\.php$/');
        foreach ($files as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);
            $stmts = $traverser->traverse($stmts);
            $code = $prettyPrinter->prettyPrintFile($stmts);
            file_put_contents($file, $code);
        }
    }

    public static function mountBoots(Event $event)
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $name = $package->getPrettyName();
        $version = $package->getPrettyVersion();
        $sanitizedVersion = static::sanitize($version);
        $baseDir = dirname($composer->getConfig()->getConfigSource()->getName());
        // $composer->getConfig()->getConfigSource()->addConfigSetting('foo', 'bar');
        $suffix = str_replace('.', '_', $sanitizedVersion);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        static::mount("{$baseDir}/temp", $suffix);
    }
}
