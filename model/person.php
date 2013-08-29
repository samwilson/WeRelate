<?php

class WeRelateCore_person extends WeRelateCore_base {

    /** @var array */
    protected $facts;

    /** @var array */
    protected $sources;

    /** @var array */
    protected $images;

    protected $tag = 'person';

    public function getFullName() {
        if (!$this->load()) return (string)$this->title;
        return (string) $this->xml->name['given'].' '.$this->xml->name['surname'];
    }

    public function getBirthDate() {
        $fact = $this->getFact('Birth');
        return $fact['date'];
    }

    public function getBirthPlace($as_place = false) {
        $fact = $this->getFact('Birth');
        return $fact['place'];
    }

    public function getDeathDate() {
        $fact = $this->getFact('Death');
        return $fact['date'];
    }

    public function getDeathPlace($as_place = false) {
        $fact = $this->getFact('Death');
        return $fact['place'];
    }

    public function getFact($fact_type) {
        foreach ($this->getFacts() as $fact) {
            if ($fact['type'] == $fact_type) {
                return $fact;
            }
        }
        return false;
    }

    public function getFamilies($type) {
        $out = array();
        $type = $type.'_of_family';
        if (isset($this->xml->$type)) {
			foreach ($this->xml->$type as $fam) {
				$title = Title::makeTitle(NS_WERELATECORE_FAMILY, $fam['title']);
				$out[] = new WeRelateCore_family($title);
			}
		}
        return $out;
    }

    public function getFacts() {
        if (is_array($this->facts)) {
            return $this->facts;
        }

        // Otherwise, get the facts
        $this->facts = array();
        if (!isset($this->xml->event_fact)) {
			return $this->facts;
        }
        foreach ($this->xml->event_fact as $fact) {
            //echo '<pre>'.print_r($fact,true).'</pre>';
            // Build general facts array
            $type = (string) $fact['type'];
            $dateSort = date('Y-m-d H:i:s', strtotime($fact['date']));

            $date = (!empty($fact['date'])) ? trim($fact['date']) : 'Date unknown';
            $desc = $fact['desc'];
            $place = $fact['place'];
            if (!empty($place)) {
                if (strpos($place, '|') === false)
                    $place .= '|' . $place;
                //$place = $this->parser->recursiveTagParse('[[Place:'.$place.']]', $this->frame);
            }
            $this->facts[$dateSort] = array(
                'type' => $type,
                'date' => $date,
                'sortDate' => $dateSort,
                'place' => $place,
                'desc' => $desc,
                'sources' => explode(', ', $fact['sources']),
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
        ksort($this->facts);
        return $this->facts;
    }

    public function getSources() {
		if (is_array($this->sources)) {
            return $this->sources;
        }
        // Otherwise, get the source citations
        $this->sources = array();
        if (!isset($this->xml->source_citation)) return $this->sources;
		foreach ($this->xml->source_citation as $source) {
			//echo'<pre>';var_dump($source);
			// <source_citation id="S6" title="Source:England and Wales. National Probate Calendar (Index of Wills and Administrations), 1858-1966">
			// Probate: Barker James Denton of 47 West Way Harpenden Hertfordshire died 30 September 1958 at 23 Lemsford Road St Albans Hertfordshire Probate 
			// London 22 January to Ronald George Taylor and Douglas James Walker solicitors Effects £7011 8s. 3d.</source_citation>
			$id = (string) $source['id'];
            //echo '<pre>'.print_r($source,true).'</pre>';
            //$title = Title::newFromText((string)$source['title']);
            //if (!$title) var_dump($title);
            //$link = Linker::link($title);
            $this->sources[$id] = array(
				'id' => $id,
                'title' => (string) $source['title'],
                'record_name' => (string) $source['record_name'],
                'body' => (string) $source,
                //'title_obj' => $title,
                //'page' => $source['page'],
                //'link' => $link,
                //'parsed' => $this->parser->recursiveTagParse('<ref name="'.$source['id'].'">'.$link.'</ref>'),
            );
		}
		return $this->sources;
    }

    public function getSource($id) {
		$sources = $this->getSources();
		return (isset($sources[$id])) ? $sources[$id] : false;
    }
    
    public function getImages() {
		if (is_array($this->images)) {
            return $this->images;
        }
        $this->images = array();
        if (isset($this->xml->image)) {
			foreach ($this->xml->image as $image) {
				$this->images[] = array(
					'id' => (string) $image['id'],
					'title' => Title::newFromText('Image:'.$image['filename']),
					'caption' => (string) $image['caption'],
					'primary' => isset($image['primary']),
				);
			}
		}
		return $this->images;
	}

	public function getPrimaryImage() {
		foreach ($this->getImages() as $image) {
			if ($image['primary']) {
				return $image;
			}
		}
		return false;
	}

    public function isChild() {
        return isset($this->xml->child_of_family['title']);
    }

    public function getTitle() {
        return $this->title;
    }
}
