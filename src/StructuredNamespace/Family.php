<?php

namespace MediaWiki\Extension\WeRelate\StructuredNamespace;

use Exception;
use Title;

class Family extends StructuredNamespace {

	/** @var string */
	protected $tag = 'family';

	/** @var array */
	protected $children;

	/**
	* Get the 'husband' or 'wife'.
	*
	* @param string $spouse
	* @return boolean|Person The person, or false if they couldn't be found.
	* @throws Exception
	*/
	public function getSpouse( $spouse ) {
		if ( !$this->load() ) { return false;
		}
		if ( !in_array( $spouse, [ 'husband', 'wife' ] ) ) {
			throw new Exception( "Spouse must be either 'husband' or 'wife'. '$spouse' given." );
		}
		if ( !isset( $this->xml->{$spouse} ) ) {
			return false;
		}
		$title = Title::makeTitle( NS_PERSON, $this->xml->{$spouse}['title'] );
		$spouse = Person::newFromTitle( $title );
		return $spouse;
	}

	/**
	 * Get the children of this family.
	 *
	 * @return Person[]
	 */
	public function getChildren() {
		if ( is_array( $this->children ) ) {
			return $this->children;
		}
		$this->children = [];
		if ( !$this->load() ) {
			return $this->children;
		}
		foreach ( $this->xml->child as $child ) {
			$title = Title::makeTitle( NS_PERSON, $child['title'] );
			$this->children[] = new Person( $title );
		}
		return $this->children;
	}

	public function getMarriageDate() {
		$fact = $this->getFact( 'Marriage' );
		return $fact['date'];
	}

}
