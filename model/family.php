<?php

class WeRelateCore_family extends WeRelateCore_base {

    protected $tag = 'family';

//    public function getFather() {
//        return $this->getSpouse();
//        $this->load();
//        var_dump($this->xml);
//        $title = Title::makeTitle(NS_WERELATECORE_PERSON, $this->xml->husband['title']);
//        $father = new WeRelateCore_person($title);
//        return $father;
//    }

    /**
     * Get the 'husband' or 'wife'.
     * 
     * @param WeRelateCore_person $spouse
     * @return boolean|\WeRelateCore_person
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
    
    public function getChildren() {
        if (!$this->load()) return false;
        $children = array();
        foreach ($this->xml->child as $child) {
            $title = Title::makeTitle(NS_WERELATECORE_PERSON, $child['title']);
            $children[] = new WeRelateCore_person($title);
        }
        return $children;
    }
}
