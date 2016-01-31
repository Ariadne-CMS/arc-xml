<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\xml;

/**
 * This is a container for raw xml content, which will not get escaped 
 * when included by the \arc\xml\Writer or NodeList.
 * @property string $contents
 */
class RawXML {
    public $contents = '';

    public function __construct($contents) 
    {
        $this->contents = $contents;
    }

    public function __toString()
    {
        return (string) $this->contents;
    }
}