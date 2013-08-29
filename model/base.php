<?php

abstract class WeRelateCore_base {

    /** @var SimpleXMLElement */
    protected $xml;

    /** @var Title */
    protected $title;

    public function __construct($title, $xml=false) {
        $this->title = $title;
        if ($xml) $this->xml = self::xmlStringToObj($xml, $this->tag);
    }

    public function pageExists() {
        $page = new WikiPage($this->title);
        return $page->exists();
    }

    public function load() {
        if ($this->xml) return true; // Already loaded
        $page = new WikiPage($this->title);
        if (!$page->exists()) return false; // Can't load
        $text = $page->getText();
        $this->xml = self::xmlStringToObj($text, $this->tag);
        return true;
    }
 
    public function getTitle() {
        return $this->title;
    }

    public function getBody() {
		if (isset($this->xml->page_body)) {
			return (string) $this->xml->page_body;
		} else {
			return false;
		}
    }

    /**
     * Turn an XML string into a SimpleXMLElement object.
     * Static for ease of use by other WeRelate extensions.
     */
	protected static function xmlStringToObj($page_text, $tag) {
        // Parse XML out of raw page text.
        $close_tag = "</$tag>";
        $start_pos = stripos($page_text, "<$tag>");
        $end_pos = stripos($page_text, $close_tag);
        if ($start_pos === FALSE OR $end_pos === FALSE) {
            return FALSE;
        }
        $xml = substr($page_text, $start_pos, $end_pos + strlen($close_tag));
        $obj = new SimpleXMLElement($xml);
        $body = substr($page_text, $end_pos + strlen($close_tag));
        $obj->addChild('page_body', htmlentities(strip_tags($body)));
        return $obj;
    }

}
