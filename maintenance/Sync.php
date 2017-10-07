<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	require_once getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php';
} else {
	require_once __DIR__ . '/../../../maintenance/Maintenance.php';
}

require_once __DIR__ . '/../src/Sync.php';
$maintClass = \MediaWiki\Extension\WeRelate\Sync::class;

// use MediaWiki\Extension\WeRelate\TreeTraverser;
//
// class syncFromRemote extends Maintenance {
//
// public function __construct() {
// parent::__construct();
// $this->requireExtension( 'WeRelate' );
// $this->mDescription = "Download selected pages from welrelate.org to this wiki.";
// $this->addOption( 'dry-run', 'Do not update database' );
// }
//
// public function execute() {
// /*
// // Get starting page names
// $title = Title::newFromText('User:Admin/sync');
// $page = WikiPage::factory($title);
// $text = $page->getContent();
//
// preg_match_all('/\[\[Person:(.*)\]\]/i', $text, $person_links);
//
// print_r($person_links);
// if (!isset($person_links[1])) {
// echo 'No links found.';
// exit(0);
// }
// $startingPages = $person_links[1];
// */
// $startingPages = [
// 'James_Barker_(50)',
// ];
//
// $treeTraverser = new TreeTraverser();
// $treeTraverser->registerCallback([$this, 'updateFromRemote']);
//
// foreach ( $startingPages as $personName) {
// echo "Traversing the family tree up and down from $personName.\n";
// $treeTraverser->ancestors($personName);
// $treeTraverser->descendants($personName);
// }
//
// }
//
// function updateFromRemote(Title $title) {
// $this->output( "Getting ".$title->getPrefixedText() );
//
// // Set up user @TODO make configurable
// $username = 'WeRelate bot';
// $user = User::newFromName($username);
// $wgUser = & $user;
// $summary = 'Importing from http://www.werelate.org';
//
// // Get local timestamp
// $page = WikiPage::factory($title);
// $local_timestamp = strtotime(($page->exists()) ? $page->getTimestamp() : 0);
// //echo "Local modified ".date('Y-m-d H:i', $local_timestamp)."\n";
//
// // Construct URL (manually, because old MW doesn't equate File to Image NS).
// $ns = ($title->getNamespace()==NS_IMAGE) ? 'Image' : $title->getNsText();
// $url = 'http://werelate.org/w/index.php?title='.$ns.':'.$title->getPartialURL().'&action=raw';
// // Get remote timestamp
// $request = $this->doWerelateRequest($url);
// $response = $request->getResponseHeaders();
// $remote_modified = (isset($response['last-modified'][0])) ? $response['last-modified'][0] : 0;
// $remote_timestamp = strtotime($remote_modified);
// //echo "Remote modified ".date('Y-m-d H:i', $remote_timestamp)."\n";
//
// // Compare local to remote
// if ($remote_modified < $local_timestamp) {
// echo "Not modified.\n";
// return;
// }
//
// // Get remote text
// $page_text = $request->getContent();
//
// // Is this an image page?
// if ($title->getNamespace() == NS_IMAGE) {
// return;
// $hash = md5($title->getDBkey());
// $url = 'http://www.werelate.org/images/'
// . substr($hash, 0, 1) . '/' . substr($hash, 0, 2) . '/'
// . $title->getPartialURL();
// echo "Getting image: $url\n";
// $tmpfile_name = tempnam(sys_get_temp_dir(), 'WeRelate');
// //echo "Saving to $tmpfile_name\n";
// $request = WeRelateRequest($url);
// file_put_contents($tmpfile_name, $request->getContent());
//
// $image = wfLocalFile($title);
// $archive = $image->publish($tmpfile_name);
// if (!$archive->isGood()) {
// echo "Could not publish file: ".$archive->getWikiText()."\n";
// }
// $image->recordUpload2($archive->value, $summary, $page_text, false, false, $user);
// echo "Saved image from $url\n";
// }
// // Or a normal non-image page?
// else {
// $content = new WikitextContent( $page_text );
// $page->doEditContent( $content, $summary, 0, false, $user );
// }
// echo "Updated ".$title->getPrefixedText()."\n";
// }
//
// public function doWerelateRequest($url) {
// $options = [ 'followRedirects' => true ];
// $httpRequest = MWHttpRequest::factory($url, $options);
// $status = $httpRequest->execute();
// if (!$status->isOK()) {
// echo $status->getWikiText();
////        foreach ($status->errors as $err) {
////            echo strtoupper($err['type']).': '.wfMessage($err['message'])->text()."\n";
////        }
////        print_r($httpRequest->getResponseHeaders());
////        echo "Unable to retrieve $url\n";
// exit(1);
// }
// return $httpRequest; //->getContent();
// }
// }

require_once DO_MAINTENANCE;
