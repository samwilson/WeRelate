<?php

class WeRelateCore_family extends WeRelateCore_base {

	/** @var string */
	protected $tag = 'family';

	/** @var array */
	protected $children;

	/**
	* Get the 'husband' or 'wife'.
	* 
	* @param WeRelateCore_person $spouse
	* @return boolean WeRelateCore_person
	* @throws Exception
	*/
	public function getSpouse($spouse) {
		if (!$this->load()) return false;
		if (!in_array($spouse, array('husband', 'wife'))) {
			throw new Exception("Spouse must be either 'husband' or 'wife'. '$spouse' given.");
		}
		if (!isset($this->xml->{$spouse})) {
			return false;
		}
		$title = Title::makeTitle(NS_WERELATECORE_PERSON, $this->xml->{$spouse}['title']);
		$spouse = new WeRelateCore_person($title);
		return $spouse;
	}

	/**
	 * Get the children of this family.
	 *
	 * @return array An array of WeRelateCore_person objects
	 */
	public function getChildren() {
		if (is_array($this->children)) {
			return $this->children;
		}
		$this->children = array();
		if (!$this->load()) {
			return $this->children;
		}
		foreach ($this->xml->child as $child) {
			$title = Title::makeTitle(NS_WERELATECORE_PERSON, $child['title']);
			$this->children[] = new WeRelateCore_person($title);
		}
		return $this->children;
	}

	public function getMarriageDate() {
		$fact = $this->getFact('Marriage');
		return $fact['date'];
	}

}
