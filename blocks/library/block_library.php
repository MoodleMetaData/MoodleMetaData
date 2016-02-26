<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * A custom block class that extends the block_base and
 * is the generic library block.
 *
 * @package library
 * @author Josh Stagg jstagg@ualberta.ca
 **/

class block_library extends block_base {

    public function init() {
        $this->title = get_string('library', 'block_library');
    }

    public function get_content() {
        if ( $this->content !== null ) {
            return $this->content;
        }

        $renderer = $this->page->get_renderer('block_library');
        $this->content = new stdClass;

        $this->content->text   = $renderer->render_library_content($this->config, $this->instance->id);
        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }
}