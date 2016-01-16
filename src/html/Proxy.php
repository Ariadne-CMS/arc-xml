<?php
/*
    TODO: implement simplexml access methods to the proxy
    probably needs to extend ArrayObject in some way to do this properly
 */
namespace arc\html;

class Proxy extends \arc\xml\Proxy {

    public function __toString() {
        $dom = dom_import_simplexml($this->target);
        $result = ''.$dom->ownerDocument->saveHTML($dom);
        echo "[".$result."]\n";
        return $result;
    }

}
