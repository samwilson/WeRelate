<?php

namespace MediaWiki\Extension\WeRelate;

use Maintenance;
use MediaWiki\Extension\WeRelate\StructuredNamespace\Person;
use MediaWiki\Extension\WeRelate\StructuredNamespace\Tree;
use MWHttpRequest;
use TextContent;
use Title;
use User;
use WikiPage;

/**
 * Maintenance class run from extensions/WeRelate/maintenance/Sync.php
 */
class Sync extends Maintenance {

	/** @var bool */
	protected $nocache;

	public function setup() {
		parent::setup();
		$this->addOption('nocache', 'Ignore the local modification time, and download anyway.', false, false, 'n');
	}

	public function execute() {
		$this->nocache = !is_null( $this->getOption('nocache') );

		// Find all pages with <printablewerelate> tags.
		$pwrPages = $this->getTreebranchPages();
		if (empty($pwrPages)) {
			$this->output("No <treebranch> elements were found in the wiki.\n");
			return 0;
		}

		// Go through the above pages to find the ancestors and descendants
		$toTraverse = [ 'ancestors'=> [], 'descendants'=> [] ];
		foreach ($pwrPages as $title) {
			$msg = "Using starting points found in: ".$title->getPrefixedText();
			$this->output("\n** $msg **\n\n");
			$treebranch = Tree::newFromTitle( $title );
			$treebranch->addObserver( [ $this, 'visitPage' ] );
			$treebranch->traverse();
		}

	}

	public function visitPage( Title $title) {
		$this->updateFromRemote($title);
		if ($title->getNamespace() == NS_PERSON) {
			$person = Person::newFromTitle($title);
			$person->load();

			// Get sources
			foreach ($person->getSources() as $source) {
				if ($title = Title::newFromText($source['title'])) { 
					$is_source = $title->getNamespace() == NS_SOURCE;
					$is_mysource = $title->getNamespace() == NS_MYSOURCE;
					if ($is_source || $is_mysource) {
						$this->updateFromRemote(Title::newFromText($source['title']));
					}
				}
			}

			// Get images
			foreach ($person->getImages() as $image) {
				$this->updateFromRemote($image['title']);
			}
		}
	}

	/**
	* Get an array of Titles of pages containing <treebranch> elements.
	* 
	* @return Title[] Array of Title objects
	*/
	private function getTreebranchPages() {
		$dbr = wfGetDB( DB_REPLICA );
		$page_table = $dbr->tableName('page');
		$revision_table = $dbr->tableName('revision');
		$text_table = $dbr->tableName('text');
		$sql = "SELECT page.page_namespace, page_title
			FROM $page_table
				INNER JOIN $revision_table ON page.page_latest = revision.rev_id
				INNER JOIN $text_table ON revision.rev_text_id = text.old_id
			WHERE text.old_text LIKE '%<tree>%</tree>%'";
		$res = $dbr->query($sql);
		$out = array();
		foreach( $res as $row ) {
			$out[] = Title::newFromText($row->page_title, $row->page_namespace);
		}
		return $out;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	function UpdateFromRemote(Title $title) {
		global $wgUser;
		$msg = sprintf("%-15s %s . . . ", $title->getNsText(), $title->getText());
		$this->output($msg);

		// Set up user @TODO make configurable
		$username = 'WeRelate bot';
		$user = User::newFromName($username);
		$wgUser = & $user;
		$summary = 'Importing from http://www.werelate.org/wiki/'.$title->getPrefixedURL();

		// Get local timestamp
		$page = WikiPage::factory($title);
		$local_timestamp = strtotime(($page->exists()) ? $page->getTimestamp() : 0);
		//echo "Local modified ".date('Y-m-d H:i', $local_timestamp)."\n";

		// Construct URL (manually, because old MW doesn't equate File to Image NS).
		$ns = ($title->getNamespace()==NS_IMAGE) ? 'Image' : $title->getNsText();
		$url = 'http://werelate.org/w/index.php?title='.$ns.':'.$title->getPartialURL().'&action=raw';
		// Get remote timestamp
		$request = $this->getHttpRequest($url);
		if (!$request) {
			return false;
		}
		$response = $request->getResponseHeaders();
		$remote_modified = (isset($response['last-modified'][0])) ? $response['last-modified'][0] : 0;
		$remote_timestamp = strtotime($remote_modified);
		//echo "Remote modified ".date('Y-m-d H:i', $remote_timestamp)."\n";

		// Compare local to remote
		if (!$this->nocache && $remote_modified < $local_timestamp) {
			$this->output("not modified.\n");
			return true;
		}

		// Get remote text
		$pageText = $request->getContent();

		// Is this an image page or other?
		if ($title->getNamespace() == NS_IMAGE) {
			$this->getAndSaveImage($title, $pageText, $summary, $user);
		} else {
			$flags = EDIT_FORCE_BOT;
			$newContent = new TextContent( $pageText, CONTENT_MODEL_WIKITEXT );
			$status = $page->doEditContent( $newContent, $summary, $flags, false, $user);
			if (!$status->isOK()) {
				$this->error($status->getWikiText(), 1);
			}
		}

		$this->output("done.\n");
	}

	protected function getAndSaveImage(Title $title, $page_text, $summary, $user) {
		$hash = md5($title->getDBkey());
		$url = 'http://www.werelate.org/images/'
				. substr($hash, 0, 1) . '/' . substr($hash, 0, 2) . '/'
				. $title->getPartialURL();
		//echo "Getting image: $url\n";
		$tmpfile_name = tempnam(sys_get_temp_dir(), 'WeRelate');
		//echo "Saving to $tmpfile_name\n";
		$request = $this->getHttpRequest($url);
		file_put_contents($tmpfile_name, $request->getContent());

		$image = wfLocalFile($title);
		$archive = $image->publish($tmpfile_name);
		if (!$archive->isGood()) {
			$this->error("Could not publish file: ".$archive->getWikiText()."\n", 1);
		}
		$image->recordUpload2($archive->value, $summary, $page_text, false, false, $user);
		//echo "Saved image from $url\n";
	}

	public function getHttpRequest($url) {
		$options = array('followRedirects' => true);
		$httpRequest = MWHttpRequest::factory($url, $options);
		$status = $httpRequest->execute();
		if (!$status->isOK()) {
			$this->error($status->getWikiText().' URL: '.$url);
			return false;
		}
		return $httpRequest;
	}

}

