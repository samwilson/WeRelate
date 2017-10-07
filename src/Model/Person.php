<?php

namespace MediaWiki\Extension\WeRelate\Model;

use MediaWiki\Extension\WeRelate\BaseModel;
use Title;

class Person extends BaseModel {

	/** @var array */
	protected $sources;

	/** @var array */
	protected $images;

	protected $tag = 'person';

	public function getFullName() {
		if ( !$this->load() ) { return (string)$this->title;
		}
		return (string)$this->xml->name['given'].' '.$this->xml->name['surname'];
	}

	public function getBirthDate() {
		$fact = $this->getFact( 'Birth' );
		return $fact['date'];
	}

	public function getBirthPlace( $as_place = false ) {
		$fact = $this->getFact( 'Birth' );
		return $fact['place'];
	}

	public function getDeathDate() {
		$fact = $this->getFact( 'Death' );
		return $fact['date'];
	}

	public function getDeathPlace( $as_place = false ) {
		$fact = $this->getFact( 'Death' );
		return $fact['place'];
	}

	/**
	 * Get all families that this person has the given relationship with.
	 *
	 * @param string $type 'spouse' or 'child'
	 * @return Family[] All the families.
	 */
	public function getFamilies( $type ) {
		$out = [];
		$type = $type.'_of_family';
		if ( isset( $this->xml->$type ) ) {
			foreach ( $this->xml->$type as $fam ) {
				$title = Title::makeTitle( NS_FAMILY, $fam['title'] );
				$out[] = new Family( $title );
			}
		}
		return $out;
	}

	public function getSources() {
		if ( is_array( $this->sources ) ) {
			return $this->sources;
		}
		// Otherwise, get the source citations
		$this->sources = [];
		if ( !isset( $this->xml->source_citation ) ) { return $this->sources;
		}
		foreach ( $this->xml->source_citation as $source ) {
			// echo'<pre>';var_dump($source);
			// <source_citation id="S6" title="Source:England and Wales. National Probate Calendar (Index of Wills and Administrations), 1858-1966">
			// Probate: Barker James Denton of 47 West Way Harpenden Hertfordshire died 30 September 1958 at 23 Lemsford Road St Albans Hertfordshire Probate
			// London 22 January to Ronald George Taylor and Douglas James Walker solicitors Effects £7011 8s. 3d.</source_citation>
			$id = (string)$source['id'];
			// echo '<pre>'.print_r($source,true).'</pre>';
			// $title = Title::newFromText((string)$source['title']);
			// if (!$title) var_dump($title);
			// $link = Linker::link($title);
			$this->sources[$id] = [
				'id' => $id,
				'title' => (string)$source['title'],
				'record_name' => (string)$source['record_name'],
				'body' => (string)$source,
				// 'title_obj' => $title,
				// 'page' => $source['page'],
				// 'link' => $link,
				// 'parsed' => $this->parser->recursiveTagParse('<ref name="'.$source['id'].'">'.$link.'</ref>'),
			];
		}
		return $this->sources;
	}

	public function getSource( $id ) {
		$sources = $this->getSources();
		return ( isset( $sources[$id] ) ) ? $sources[$id] : false;
	}

		public function getImages() {
		if ( is_array( $this->images ) ) {
			return $this->images;
		}
		$this->images = [];
		if ( isset( $this->xml->image ) ) {
			foreach ( $this->xml->image as $image ) {
				$this->images[] = [
					'id' => (string)$image['id'],
					'title' => Title::newFromText( 'Image:'.$image['filename'] ),
					'caption' => (string)$image['caption'],
					'primary' => isset( $image['primary'] ),
				];
			}
		}
		return $this->images;
	 }

	public function getPrimaryImage() {
		foreach ( $this->getImages() as $image ) {
			if ( $image['primary'] ) {
				return $image;
			}
		}
		return false;
	}

	public function isChild() {
		return isset( $this->xml->child_of_family['title'] );
	}

	public function getTitle() {
		return $this->title;
	}
}
