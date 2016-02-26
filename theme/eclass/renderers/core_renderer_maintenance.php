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
 * @package eclass-theme-bootstrap-uofa
 * @author joshstagg
 * @copyright Josh Stagg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_eclass_core_renderer_maintenance extends core_renderer_maintenance {
    public function eclass_header($columns) {
            global $CFG, $SITE;
            $output = html_writer::start_tag('header', array("role" => "banner",
                "class" => "navbar navbar-fixed-top moodle-has-zindex"));
            $output .= html_writer::start_tag('nav', array("role" => "navigation", "class" => "navbar-inner"));
            $output .= html_writer::start_div("container-fluid");
            $output .= html_writer::tag('img', '',
                array("src" => $this->pix_url('ua-logo', 'theme'), "class" => "uofa-logo", "height" => "40px"));
            $output .= html_writer::link($CFG->wwwroot, $SITE->shortname, array("class" => "brand"));

            $output .= html_writer::end_div();
            $output .= html_writer::end_tag('nav');
            $output .= html_writer::end_tag('header');
            return $output;
        }
}