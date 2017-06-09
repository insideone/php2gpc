<?php

namespace Inside\PhpToGpc;

use PhpParser\Node;

class NodeException extends \Exception
{
    public function __construct($message, Node $node)
    {
        parent::__construct("{$message} (in line {$node->getLine()})");
    }
}