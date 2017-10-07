<?php

namespace MediaWiki\Extension\WeRelate\Model;

use MediaWiki\Extension\WeRelate\BaseModel;

class Mysource extends BaseModel {

	protected $tag = 'mysource';

	public function getAuthor() {
		return $this->xml->author;
	}

	public function getSurnames() {
		return $this->xml->surname;
	}

	public function getPublicationInfo() {
		return $this->xml->publication_info;
	}
}
