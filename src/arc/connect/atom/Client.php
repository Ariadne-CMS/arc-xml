<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\connect\atom;

	class Client extends \arc\xml\DataBinding {

		private $httpClient = null;
		private $feed = null;

		public function __construct( $feed = null, $httpClient = null ) {
			$this->feed = $feed;
			$this->httpClient = $httpClient;
			if ($feed && $this->httpClient) {
				$this->get( $feed );
			}
		}

		public function get( $url, $request = null, $options = array() ) {
			$xml = $this->httpClient->get( $url, $request, $options );
			return $this->parse( $xml );
		}

		public function parse( $xml ) {
			$dom = \arc\xml::parse( $xml );
			$feed = $dom->feed[0];
			$this->bind( $feed->title, 'title' )
				->bind( $feed->link->getAttribute( 'href' ), 'link' )
				->bind( $feed->subtitle, 'subtitle' )
				->bind( $feed->id, 'id' )
				->bind( $feed->updated, 'updated' )
				->bind( $feed->author->name, 'authorName' )
				->bind( $feed->author->email, 'authorEmail' )
				->bindAsArray( $feed->entry, 'items', 'self::parseItem' )
				->bind( $dom, 'rawXML', 'xml' );
			return $this;
		}

		public function parseItem( $item ) {
			return $item->bind( $item->title, 'title' )
					->bind( $item->link->getAttribute( 'href' ), 'link' )
					->bind( $item->summary, 'summary' )
					->bind( $item->updated, 'updated' )
					->bind( $item->id, 'id' )
					->bind( $item->content, 'content' )
					->bind( $item->author->name, 'authorName' )
					->bind( $item->author->email, 'authorEmail' )
					->bind( $item, 'rawXML', 'xml' );
		}

	}
?>