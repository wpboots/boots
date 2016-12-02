<?php

namespace Boots\Installer;

/**
 * Node visitor for boots installer.
 *
 * @package Boots
 * @subpackage Installer\NodeVisitor
 * @version 2.0.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * @package Boots
 * @subpackage Installer\NodeVisitor
 * @version 2.0.0
 */
class NodeVisitor extends NodeVisitorAbstract
{
    protected $version;

    protected $fqcn;

    public function __construct($version)
    {
        $this->version = str_replace('.', '_', $version);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {
            $this->fqcn = $node->namespacedName->toString();
            $classParts = explode('\\', $this->fqcn);
            $classname = $classParts[count($classParts)-1];
            $node->name = "{$classname}_{$this->version}";
        }
    }

    public function getFqcn()
    {
        return $this->fqcn;
    }
}