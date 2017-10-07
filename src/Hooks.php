<?php
/**
 * @file
 */

namespace MediaWiki\Extension\WeRelate;

use OutputPage;
use Parser;
use Skin;

/**
 * Static hook function for WeRelate.
 */
class Hooks {

	public static function onParserFirstCallInit( Parser $parser ) {
		$tag = new Templates();
		$tag->init( $parser );
	}

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) { 
		$out->addModules( 'ext.WeRelate' );
	}
}
