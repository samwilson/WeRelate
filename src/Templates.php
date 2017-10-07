<?php

namespace MediaWiki\Extension\WeRelate;

use MediaWiki\MediaWikiServices;
use Parser;
use Symfony\Component\VarDumper\Caster\FrameStub;
use TitleValue;

/**
 * A Tag is any of WeRelate's XML tags.
 */ 
class Templates {

	/**
	 * @var array The definitive set of top-level tags supported by this extension.
	 */
	protected $tags = [
		'person',
		'family',
		'place',
		'show_sources_images_notes',
		'source',
		'mysource',
	];

	public function init( Parser $parser ) {
		foreach ( $this->tags as $tag ) {
			$parser->setHook( $tag, [ $this, $tag ] );
		}
	}

	public function __call( $tag, $arguments ) {
		$input = array_shift( $arguments );
		$args = array_shift( $arguments );
		$parser = array_shift( $arguments );
		$frame = array_shift( $arguments );
		return $this->toHtml( $parser, $frame, $tag, $input );
	}

	public function toHtml( Parser $parser, $frame, $tag, $input ) {
		// Construct the class name based on the tag name.
		$tagClassname = str_replace('_', '', ucwords( $tag, '_' ) );
		$classname = 'MediaWiki\\Extension\\WeRelate\\Model\\' . $tagClassname;
		if ( !class_exists( $classname ) ) {
			return "<span class='error'>Can not find $classname</span>";
		}

		// Try to find the template.
		$templateFile = __DIR__ . '/../templates/' . $tag . '.html.php';
		if ( !file_exists( $templateFile ) ) {
			return "<span class='error'>Unable to find template '$templateFile' for '$tag' tag.</span>";
		}

		// Add modules.
		//$parser->getOutput()->addModules( 'ext.WeRelate' );

		// Create a new instance of that class, and pass it to the template.
		// NOTE: the use of a variable variable name!
		$text = "<$tag>$input</$tag>";
		$$tag = new $classname( $parser->getTitle(), $text );
		ob_start();
		require_once $templateFile;
		$html = ob_get_clean();

		return '<div class="ext-werelate">' . trim( $html ) . '</div>';
	}

	/**
	 * Convenience wrapper for getting a link.
	 * @param $namespace
	 * @param $dbKey
	 * @param string $fragment
	 * @param string $interwiki
	 * @return string
	 */
	public function link( $namespace, $dbKey, $fragment = '', $interwiki = '' ) {
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$value = new TitleValue( $namespace, $dbKey, $fragment, $interwiki );
		return $linkRenderer->makeLink( $value );
	}
}
