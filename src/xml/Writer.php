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
 * This class allows you to create valid and nicely indented XML strings
 * Any method called on it is interpreted as a new XML element to create.
 */
class Writer {

    /**
     * @var string $indent The string to ident each level with. Default is a tab.
     */
    public $indent = "\t";

    /**
     * @var string $newLine The string to use as a new line or linebreak. Defaults to \r\n.
     */
    public $newLine = "\r\n";

    /**
     * @param array $options allows you to set the indent and newLine
     * options immediately upon construction
     */
    public function __construct( $options = [])
    {
        $optionList = [ 'indent', 'newLine' ];
        foreach( $options as $option => $optionValue) {
            if (in_array( $option, $optionList )) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function __call( $name, $args)
    {	
        return call_user_func_array( [ new NodeList( [], $this ), $name ], $args );
    }

}
