<?php
/**
 * @file
 */

namespace MediaWiki\Extension\WeRelate;

use EditPage;
use MediaWiki\Extension\WeRelate\StructuredNamespace\Family;
use MediaWiki\Extension\WeRelate\StructuredNamespace\MySource;
use MediaWiki\Extension\WeRelate\StructuredNamespace\Person;
use MediaWiki\Extension\WeRelate\StructuredNamespace\ShowSourcesImagesNotes;
use MediaWiki\Extension\WeRelate\StructuredNamespace\StructuredNamespace;
use MediaWiki\Extension\WeRelate\StructuredNamespace\Tree;
use OutputPage;
use Parser;
use Skin;
use WebRequest;

/**
 * Static hook function for WeRelate.
 */
class Hooks {

	public static function onParserFirstCallInit( Parser $parser ) {
		Family::setHook( $parser );
		MySource::setHook( $parser );
		Person::setHook( $parser );
		ShowSourcesImagesNotes::setHook( $parser );
		Tree::setHook( $parser );
	}

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) { 
		$out->addModules( 'ext.WeRelate' );
	}

	public static function onEditPageShowEditFormFields( EditPage $editpage, OutputPage $output ) {
		$model = StructuredNamespace::newFromEditPage( $editpage );
		if ( $model instanceof StructuredNamespace ) {
			$output->enableOOUI();
			$output->addHTML( $model->getForm() );
		}
		return true;
	}

	public static function onEditPageImportFormData( EditPage $editpage, WebRequest $request ) {
		$model = StructuredNamespace::newFromEditPage( $editpage );
		if ( $model instanceof StructuredNamespace ) {
			$model->updateFromRequest( $request );
			if ($editpage->action == 'submit' && !$editpage->preview) {
				$editpage->textbox1 = $model->getFullWikitext();
			}
		}
	}
}
