<?php

namespace MediaWiki\Extension\WeRelate;

use Exception;
use SimpleXMLElement;
use Title;
use WikiPage;

abstract class BaseModel {

	/** @var SimpleXMLElement */
	protected $xml;

	/** @var Title */
	protected $title;

	/** @var array */
	protected $facts;

	/** @var string The name of the top-level tag. */
	protected $tag;

	public function __construct( $title, $xml = false ) {
		if ( is_null( $this->tag ) ) {
			throw new Exception( 'Please set $tag on ' . get_class( $this ) );
		}
		$this->title = $title;
		if ( $xml ) {
			$this->xml = self::xmlStringToObj( $xml, $this->tag );
		}
	}

	public function pageExists() {
		$page = new WikiPage( $this->title );
		return $page->exists();
	}

	public function load() {
		if ( $this->xml ) {
			return true;
		}
		// Already loaded
		$page = new WikiPage( $this->title );
		if ( !$page->exists() ) {
			return false;
		}
		// Can't load.
		$text = $page->getContent()->getNativeData();
		$this->xml = self::xmlStringToObj( $text, $this->tag );

		return true;
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	public function getBody() {
		if ( isset( $this->xml->page_body ) ) {
			return (string)$this->xml->page_body;
		} else {
			return false;
		}
	}

	public function getFact( $fact_type ) {
		foreach ( $this->getFacts() as $fact ) {
			if ( $fact['type'] == $fact_type ) {
				return $fact;
			}
		}

		return false;
	}

	public function getFacts() {
		if ( is_array( $this->facts ) ) {
			return $this->facts;
		}
		if ( !$this->load() ) {
			return false;
		}

		// Otherwise, get the facts
		$this->facts = array();
		if ( !isset( $this->xml->event_fact ) ) {
			return $this->facts;
		}
		foreach ( $this->xml->event_fact as $fact ) {
			//echo '<pre>'.print_r($fact,true).'</pre>';
			// Build general facts array
			$type = (string)$fact['type'];
			$dateSort = date( 'Y-m-d H:i:s', strtotime( $fact['date'] ) );

			$date = ( !empty( $fact['date'] ) ) ? trim( $fact['date'] ) : 'Date unknown';
			$desc = $fact['desc'];
			$place = $fact['place'];
			if ( !empty( $place ) ) {
				if ( strpos( $place, '|' ) === false ) {
					$place .= '|' . $place;
				}
			}
			$this->facts[$dateSort] = array(
				'type' => $type,
				'date' => $date,
				'sortDate' => $dateSort,
				'place' => $place,
				'desc' => $desc,
				'sources' => explode( ', ', $fact['sources'] ),
			);
			/* // Define some convenience variables.
			  if ($type=='Birth') {
			  $birthDate = $facts[$dateSort]['date'];
			  $birthPlace = $facts[$dateSort]['place'];
			  }
			  if ($type=='Death') {
			  $deathDate = $facts[$dateSort]['date'];
			  $deathPlace = $facts[$dateSort]['place'];
			  } */
		}
		ksort( $this->facts );

		return $this->facts;
	}

	/**
	 * Turn an XML string into a SimpleXMLElement object.
	 * Static for ease of use by other WeRelate extensions.
	 */
	protected static function xmlStringToObj( $page_text, $tag ) {
		// Parse XML out of raw page text.
		$close_tag = "</$tag>";
		$start_pos = stripos( $page_text, "<$tag>" );
		$end_pos = stripos( $page_text, $close_tag );
		if ( $start_pos === FALSE OR $end_pos === FALSE ) {
			return FALSE;
		}
		$xml = substr( $page_text, $start_pos, $end_pos + strlen( $close_tag ) );
		$obj = new SimpleXMLElement( $xml );
		$body = substr( $page_text, $end_pos + strlen( $close_tag ) );
		$obj->addChild( 'page_body', htmlentities( strip_tags( $body ) ) );

		return $obj;
	}

}
