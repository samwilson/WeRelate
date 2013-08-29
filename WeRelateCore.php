<?php
if (!defined('MEDIAWIKI')) die(0);

/**
 * Extension metadata
 */
$wgExtensionCredits['other'][] = array(
    'path' => __FILE__,
    'name' => 'WeRelateCore',
    'author' => "Sam Wilson <[mailto:sam@samwilson.id.au sam@samwilson.id.au]>",
    'url' => "http://www.mediawiki.org/wiki/Extension:WeRelate",
    'descriptionmsg' => 'werelatecore-desc',
    'version' => 2.0,
);

/**
 * Messages
 */
$wgExtensionMessagesFiles['WeRelateCore'] = __DIR__ . '/WeRelateCore.i18n.php';
$wgExtensionMessagesFiles['WeRelateCoreNamespaces'] = __DIR__ . '/WeRelateCore.namespaces.php';

/**
 * Namespace definitions
 */
define('NS_WERELATECORE_GIVEN_NAME', 100);
define('NS_WERELATECORE_GIVEN_NAME_TALK', 101);
define('NS_WERELATECORE_SURNAME', 102);
define('NS_WERELATECORE_SURNAME_TALK', 103);
define('NS_WERELATECORE_SOURCE', 104);
define('NS_WERELATECORE_SOURCE_TALK', 105);
define('NS_WERELATECORE_PLACE', 106);
define('NS_WERELATECORE_PLACE_TALK', 107);
define('NS_WERELATECORE_PERSON', 108);
define('NS_WERELATECORE_PERSON_TALK', 109);
define('NS_WERELATECORE_FAMILY', 110);
define('NS_WERELATECORE_FAMILY_TALK', 111);
define('NS_WERELATECORE_MYSOURCE', 112);
define('NS_WERELATECORE_MYSOURCE_TALK', 113);
define('NS_WERELATECORE_REPOSITORY', 114);
define('NS_WERELATECORE_REPOSITORY_TALK', 115);
define('NS_WERELATECORE_PORTAL', 116);
define('NS_WERELATECORE_PORTAL_TALK', 117);
define('NS_WERELATECORE_TRANSCRIPT', 118);
define('NS_WERELATECORE_TRANSCRIPT_TALK', 119);

// Content namespaces (i.e. non-talk ones, really)
$wgContentNamespaces = array_merge($wgContentNamespaces, array(
  NS_WERELATECORE_GIVEN_NAME,
  NS_WERELATECORE_SURNAME,
  NS_WERELATECORE_SOURCE,
  NS_WERELATECORE_PLACE,
  NS_WERELATECORE_PERSON,
  NS_WERELATECORE_FAMILY,
  NS_WERELATECORE_MYSOURCE,
  NS_WERELATECORE_REPOSITORY,
  NS_WERELATECORE_PORTAL,
  NS_WERELATECORE_TRANSCRIPT,
));

// To be searched by default
$wgNamespacesToBeSearchedDefault = array_merge($wgNamespacesToBeSearchedDefault, array(
  NS_WERELATECORE_GIVEN_NAME => true,
  NS_WERELATECORE_SURNAME => true,
  NS_WERELATECORE_SOURCE => true,
  NS_WERELATECORE_PLACE => true,
  NS_WERELATECORE_PERSON => true,
  NS_WERELATECORE_FAMILY => true,
  NS_WERELATECORE_MYSOURCE => true,
  NS_WERELATECORE_REPOSITORY => true,
  NS_WERELATECORE_PORTAL => true,
  NS_WERELATECORE_TRANSCRIPT => true,
));

/**
 * Class loading
 */
$wgAutoloadClasses['WeRelateCore_Tags'] = __DIR__.'/Tags.php';
$wgAutoloadClasses['WeRelateCore_base'] = __DIR__.'/model/base.php';
$wgAutoloadClasses['WeRelateCore_person'] = __DIR__.'/model/person.php';
$wgAutoloadClasses['WeRelateCore_family'] = __DIR__.'/model/family.php';
$wgAutoloadClasses['WeRelateCore_show_sources_images_notes'] = __DIR__.'/model/show_sources_images_notes.php';

/**
 * Canonical namespaces (hooked below). This should be kept up to date with the
 * names defined in WeRelateCore.namespaces.php
 */
function WeRelateCore_CanonicalNamespaces( &$list ) {
	$list[NS_WERELATECORE_GIVEN_NAME] = 'Given_Name';
	$list[NS_WERELATECORE_GIVEN_NAME_TALK] = 'Given_Name_Talk';
	$list[NS_WERELATECORE_SURNAME] = 'Surname';
	$list[NS_WERELATECORE_SURNAME_TALK] = 'Surname_Talk';
	$list[NS_WERELATECORE_SOURCE] = 'Source';
	$list[NS_WERELATECORE_SOURCE_TALK] = 'Source_Talk';
	$list[NS_WERELATECORE_PLACE] = 'Place';
	$list[NS_WERELATECORE_PLACE_TALK] = 'Place_Talk';
	$list[NS_WERELATECORE_PERSON] = 'Person';
	$list[NS_WERELATECORE_PERSON_TALK] = 'Person_Talk';
	$list[NS_WERELATECORE_FAMILY] = 'Family';
	$list[NS_WERELATECORE_FAMILY_TALK] = 'Family_Talk';
	$list[NS_WERELATECORE_MYSOURCE] = 'MySource';
	$list[NS_WERELATECORE_MYSOURCE_TALK] = 'MySource_Talk';
	$list[NS_WERELATECORE_REPOSITORY] = 'Repository';
	$list[NS_WERELATECORE_REPOSITORY_TALK] = 'Repository_Talk';
	$list[NS_WERELATECORE_PORTAL] = 'Portal';
	$list[NS_WERELATECORE_PORTAL_TALK] = 'Portal_Talk';
	$list[NS_WERELATECORE_TRANSCRIPT] = 'Transcript';
	$list[NS_WERELATECORE_TRANSCRIPT_TALK] = 'Transcript_Talk';
    return true;
}

/**
 * Hooks
 */
$wgHooks['CanonicalNamespaces'][] = 'WeRelateCore_CanonicalNamespaces';
$tags = new WeRelateCore_Tags();
$wgHooks['ParserFirstCallInit'][] = array($tags, 'init');

