<?php

namespace MediaWiki\Extension\WeRelate\StructuredNamespace;

use Title;

class Tree extends StructuredNamespace {

	protected $tag = 'tree';
	
	protected $formFields = [
		'ancestor' => [
			'multiline' => true,
		],
		'descendent' => [
			'multiline' => true,
		],
	];

	protected $observers = [];

	public function getUrlForPdfDownload() {
		$title = Title::newFromText( 'Special:WeRelate/'.$this->getTitle()->getPrefixedURL() );
		return $title->getFullURL();
	}

	public function addObserver( $callback ) {
		$this->observers[] = $callback;
	}

	protected function notify( $title ) {
		foreach ( $this->observers as $callback ) {
			call_user_func( $callback, $title );
		}
	}

	public function traverse() {
		$this->load();
		foreach ( $this->xml->ancestor as $a ) {
			$ancestor = Title::makeTitle( NS_PERSON, (string)$a );
			$this->traverseAncestors( $ancestor );
		}
		foreach ( $this->xml->descendant as $d ) {
			$descendant = Title::makeTitle( NS_PERSON, (string)$d );
			$this->traverseDescendants( $descendant );
		}
	}

	protected function traverseAncestors( Title $ancestor ) {
		$this->notify( $ancestor );
		$person = Person::newFromTitle( $ancestor );
		if ( !$person->load() ) { return;
}		foreach ( $person->getFamilies( 'child' ) as $family ) {
			$this->notify( $family->getTitle() );
			if ( $h = $family->getSpouse( 'husband' ) ) { 
				$this->traverseAncestors( $h->getTitle() );
			}
			if ( $w = $family->getSpouse( 'wife' ) ) {
				$this->traverseAncestors( $w->getTitle() );
			}
  }
	}

	protected function traverseDescendants( Title $descendant ) {
		$this->notify( $descendant );
		$person = Person::newFromTitle( $descendant );
		if ( !$person->load() ) {
			return;
		}
		foreach ( $person->getFamilies( 'spouse' ) as $family ) {
			$this->notify( $family->getTitle() );
			foreach ( $family->getChildren() as $child ) {
				$this->traverseDescendants( $child->getTitle() );
			}
		}
	}

	public function getAncestors() {
		if ( !$this->load() ) { return false;
		}
		$ancestors = [];
		foreach ( $this->xml->ancestors as $a ) {
			$title = Title::makeTitle( NS_WERELATECORE_PERSON, $a );
			$ancestors[] = Person::newFromTitle( $title );
		}
		return $ancestors;
	}

	public function getDescendants() {
		if ( !$this->load() ) { return false;
		}
		$descendants = [];
		foreach ( $this->xml->descendants as $d ) {
			$title = Title::makeTitle( NS_WERELATECORE_PERSON, $d );
			$descendants[] = Person::newFromTitle( $title );
		}
		return $descendants;
	}

}
