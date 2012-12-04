<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\tree;

/**
 * abstract class to document minimum tree node requirements for a node to work with the static \arc\tree methods
 * there is absolutely no reason to extend this class, its here to support some IDE's code comprehension.
 */
abstract class Node {

    public $parentNode = null;
    public $childNodes = array();
    public $nodeValue = null;

}