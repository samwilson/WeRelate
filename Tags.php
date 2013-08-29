<?php

class WeRelateCore_Tags {

	/**
	 * @var string
	 */
	protected $extension = 'WeRelateCore';

    /**
     * @var array The definitive set of top-level tags supported by this extension.
     */
    protected $tags = array(
        'person',
        'family',
        'show_sources_images_notes'
    );

    /**
     * @var string
     */
    protected $tag;

    public function init(Parser $parser) {
        foreach ($this->tags as $tag) {
            $parser->setHook($tag, array($this, $tag));
        }
        return true;
    }

    public function __call($tag, $arguments) {
        $input = array_shift($arguments);
        $args = array_shift($arguments);
        $parser = array_shift($arguments);
        $frame = array_shift($arguments);
        $this->tag = $tag;
        $this->input = $input;
        $this->parser = $parser;
        $this->frame = $frame;
        return $this->toHtml();
    }

    public function toHtml() {
		global $IP;
        $text = "<$this->tag>$this->input</$this->tag>";
        $frame = $this->frame;
        $parser = $this->parser;
        $model_classname = $this->extension.'_'.$this->tag;
        if (!class_exists($model_classname)) {
            return 'Can not find '.$model_classname;
        }
        $model_name = $this->tag;
        $$model_name = new $model_classname($this->parser->getTitle(), $text);
        ob_start();
        $html_file = $IP.DIRECTORY_SEPARATOR.'extensions'
			.DIRECTORY_SEPARATOR.$this->extension
			.DIRECTORY_SEPARATOR.'output'
			.DIRECTORY_SEPARATOR.$this->tag.'.html.php';
        require_once $html_file;
        $html = ob_get_clean();
        return trim($html);
    }

}
