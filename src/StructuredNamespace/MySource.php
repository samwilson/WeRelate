<?php

namespace MediaWiki\Extension\WeRelate\StructuredNamespace;

class MySource extends StructuredNamespace {

	protected $tag = 'mysource';
	
	protected $formFields = [
		'place' => [
			'multiline' => true,
			'rows' => 5,
			'help' => 'One per line'
		],
		'surname' => [
			'multiline' => true,
			'rows' => 5,
			'help' => 'One per line'
		],
		'from_year' => [],
		'to_year' => [],
		'url' => [],
		'author' => [],
		'publication_info' => [],
		'call_number' => [],
		'type' => [],
		'repository_name' => [],
		'repository_addr' => [],
		'abbrev' => [],
	];

}
