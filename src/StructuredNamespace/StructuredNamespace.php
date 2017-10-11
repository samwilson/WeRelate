<?php

namespace MediaWiki\Extension\WeRelate\StructuredNamespace;

use EditPage;
use Exception;
use MWNamespace;
use OOUI\FieldLayout;
use OOUI\TextInputWidget;
use Parser;
use PPFrame;
use SimpleXMLElement;
use Title;
use WebRequest;
use WikiPage;

/**
 * This is the parent class of all classes that represent WeRelate namespaces.
 */
abstract class StructuredNamespace {

	/** @var SimpleXMLElement */
	protected $xml;
	
	/** @var string The main body wikitext, without the frontmatter XML. */
	protected $body;

	/** @var Title */
	protected $title;

	/** @var array */
	protected $facts;

	/** @var string The name of the top-level tag. Subclasses MUST set this. */
	protected $tag;

	/**
	 * Set the parser tag for this StructuredNamespace. 
	 * @param Parser $parser The parser to attach it to.
	 */
	public static function setHook( Parser $parser ) {
		$me = new static();
		$parser->setTitle( $parser->getTitle() );
		$parser->setHook( $me->tag, [$me, 'getTagDisplay'] );
	}

	/**
	 * @param Title $title
	 * @return StructuredNamespace|false The model, or false if none apply.
	 */
	public static function newFromEditPage( EditPage $editPage) {
		$nsName = MWNamespace::getCanonicalName( $editPage->getTitle()->getNamespace() );
		$classname = __NAMESPACE__ . '\\' .$nsName;
		if ( !class_exists( $classname ) ) {
			return false;
		}
		return new $classname( $editPage );
	}

	/**
	 * @param Title $title
	 * @return static|false
	 */
	public static function newFromTitle( Title $title ) {
		$nsName = MWNamespace::getCanonicalName( $title->getNamespace() );
		$classname = __NAMESPACE__ . '\\' . $nsName;
		$model = new $classname();
		$model->setTitle( $title );
		return $model;
	}

	protected function __construct( EditPage $editPage = null ) {
		if ( is_null( $this->tag ) ) {
			throw new Exception( 'Please set ' . get_class( $this ) . '::$tag' );
		}

		if ( is_null( $editPage ) ) {
			return $this;
		}

		$this->title = $editPage->getTitle();
		
		$this->loadFromText( $editPage->textbox1 );
		$editPage->textbox1 = $this->getBody();
	}

	protected function loadFromText( $text ) {
		$start = strpos($text, "<$this->tag>");
		$end = strpos($text, "</$this->tag>", $start);
		if ( $start !== false && $end !== false ) {
			$xmlLength = $end + 3 + strlen( $this->tag ) - $start;
			$this->xml = new SimpleXMLElement( substr( $text, $start, $xmlLength ) );
			$this->body = substr( $text, $xmlLength + 1 );
		} else {
			// If no XML element found, create an empty one and use the full text as the body.
			$this->xml = new SimpleXMLElement("<$this->tag></$this->tag>");
			$this->body = $text;
		}
		
//		if ( $xml ) {
//			$this->xml = self::xmlStringToObj( $xml, $this->tag );
//		}
	}

	/**
	 * Get the output HTML for this tag.
	 * @param $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function getTagDisplay( $input, array $args, Parser $parser, PPFrame $frame ) {
		$this->loadFromText("<$this->tag>$input</$this->tag>");
		$this->title = $parser->getTitle();
		// Try to find the template.
		$templateFile = __DIR__ . '/../../templates/' . $this->tag . '.html.php';
		if ( !file_exists( $templateFile ) ) {
			return "<span class='error'>Unable to find template '$templateFile' for '$this->tag' tag.</span>";
		}

		// Add modules.
		//$parser->getOutput()->addModules( 'ext.WeRelate' );

		// Create a new instance of that class, and pass it to the template.
		// NOTE: the use of a variable variable name!
//		$text = "<$this->tag>$input</$tag>";
//		$$tag = new $classname( $parser->getTitle(), $text );
		ob_start();
		require_once $templateFile;
		$html = ob_get_clean();

		return '<div class="ext-werelate">' . trim( $html ) . '</div>';
	}

//	/**
//	 * Convenience wrapper for getting a link.
//	 * @param int $namespaceId
//	 * @param string $dbKey
//	 * @param string $fragment
//	 * @param string $interwiki
//	 * @return string
//	 */
//	public function getLink( $namespaceId, $dbKey, $fragment = '', $interwiki = '' ) {
//		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
//		$value = new TitleValue( $namespaceId, (string)$dbKey, $fragment, $interwiki );
//		return $linkRenderer->makeLink( $value );
//	}

	public function pageExists() {
		$page = new WikiPage( $this->title );
		return $page->exists();
	}
	
	public function getFullWikitext() {
		$xml = substr( $this->xml->asXML(), strlen('<?xml version="1.0"?>') + 1 );
		return $xml.$this->body;
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

	public function setTitle( Title $title ) {
		$this->title = $title;
	}

	public function getBody() {
		return $this->body;
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
	 */
	protected function xmlStringToObj( $page_text, $tag ) {
		// Parse XML out of raw page text.
		$close_tag = "</$tag>";
		$start_pos = stripos( $page_text, "<$tag>" );
		$end_pos = stripos( $page_text, $close_tag );
		if ( $start_pos === FALSE || $end_pos === FALSE ) {
			return FALSE;
		}
		$xml = substr( $page_text, $start_pos, $end_pos + strlen( $close_tag ) );
		$obj = new SimpleXMLElement( $xml );
		$this->body = substr( $page_text, $end_pos + strlen( $close_tag ) );
		return $obj;
	}


	protected function getValueFromXml( $el ) {
		$val = '';
		foreach ($this->xml->$el as $element) {
			$val .= "$element\n";
		}
		return trim( $val );
//		if ( $this->xml->$el->count > 1) {
//		}
//		return $this->xml->$el;
	}

	/**
	 * Get the form HTML.
	 * @return string
	 */
	public function getForm() {
		if (!isset($this->formFields)) {
			return;
		}
		$fields = '';
		foreach ( $this->formFields as $name => $config ) {
			// Widget PHP class.
			$widgetClass = isset( $config['widgetclass']) ? $config['widgetclass'] : TextInputWidget::class;
			// Name.
			if (!isset($config['name'])) {
				$config['name'] = $name;
			}
			// Value.
			$config['value'] = $this->getValueFromXml( $name );
			// Build widget and label.
			$input = new $widgetClass( $config );
			$msg = wfMessage( 'werelate-field-label-' . $name );
			$layoutConfig = [ 'label' => $msg->text(), 'align' => 'right' ];
			if ( isset( $config['help'] ) ) {
				$layoutConfig['help'] = $config['help'];
			}
			$layout = new FieldLayout( $input, $layoutConfig );
			// Save for output.
			$fields .= $layout->toString();
		}
		return $fields;
	}

	/**
	 * @param WebRequest $request
	 * @return mixed
	 */
	public function updateFromRequest( WebRequest $request ) {
		if (!isset($this->formFields)) {
			return;
		}
		foreach ( $this->formFields as $name => $config ) {
			if (isset($config['multiline'])) {
				foreach ( explode("\n", $request->getText( $name ) ) as $lineValue ) {
					if ( strlen( $lineValue ) > 0 ) {
						$this->xml->addChild( $name, $lineValue );
					}
				}
			} else {
				$this->xml->addChild( $name, $request->getText( $name ) );
			}
		}
	}

}
