<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\connect\rss;

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
			$channel = $dom->rss->channel[0];
			$this->bind( $channel->title, 'title' )
				->bind( $channel->link, 'link' )
				->bind( $channel->description, 'description' )
				->bind( $channel->language, 'language' )
				->bind( $channel->copyright, 'copyright' )
				->bind( $channel->managingEditor, 'managingEditor' )
				->bind( $channel->webMaster, 'webMaster' )
				->bind( $channel->pubDate, 'pubDate' )
				->bind( $channel->lastBuildDate, 'lastBuildDate' )
				->bind( $channel->category, 'category' )
				->bind( $channel->generator, 'generator' )
				->bind( $channel->cloud->attributes, 'cloud', 'array' )
				->bind( $channel->ttl, 'ttl', 'int' )
				->bind( $channel->image->attributes, 'image', 'array' )
				->bindAsArray( $channel->item, 'items', 'self::parseItem' )
				->bind( $dom, 'rawXML', 'xml' );
			return $this;
		}

		public function parseItem( $item ) {
			return $item->bind( $item->title, 'title')
				->bind( $item->link, 'link' )
				->bind( $item->description, 'description')
				->bind( $item->guid, 'guid' )
				->bind( $item->author, 'author' )
				->bind( $item->category, 'category' )
				->bind( $item->comments, 'comments' )
				->bind( $item->enclosure->attributes, 'enclosure', 'array' )
				->bind( $item->source->nodeValue, 'source' )
				->bind( $item->source->getAttribute('url'), 'source_url' )
				->bind( $item->pubDate, 'pubDate')	
				->bind( $item, 'rawXML', 'xml' );
		}

	}
?>