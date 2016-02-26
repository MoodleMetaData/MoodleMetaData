<?php

class block_usingmoodle extends block_base {

	public function init() {
		$this->title = get_string('usingmoodle', 'block_usingmoodle');
	}

	public function get_content() {
	    if ( $this->content !== null ) {
	        return $this->content;
	    }

        $renderer = $this->page->get_renderer('block_usingmoodle');


        $footer = '';

        $this->content         =  new stdClass;
        $this->content->footer = $footer;
        $this->content->text   = $renderer->render_block();

	    return $this->content;
	}

    function has_config() {
        return true;
    }

    public function instance_allow_multiple() {
        return false;
    }

}