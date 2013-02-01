<?php
/*
	TODO: implement simplexml access methods to the proxy
	probably needs to extend ArrayObject in some way to do this properly
*/
namespace arc\xml;

class Proxy {

	use \arc\traits\Proxy {
		\arc\traits\Proxy::__construct as private ProxyConstruct
	}	

	private $parser = null;

	public function __construct( $node, $parser ) {
		ProxyConstruct( $node );
		$this->parser = $parser;
	}

}