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
 * A custom renderer class that extends the plugin_renderer_base and
 * is used by the generic usingmoodle block.
 *
 * @package usingmoodle
 * @author Anthony Radziszewski radzisze@ualberta.ca
 **/

class block_usingmoodle_renderer extends plugin_renderer_base {

    public function render_block() {
        $img = html_writer::tag('img', '', array(
            'alt' => 'Moodle',
            'style' => 'float: right;',
            'src' => 'https://sites.google.com/a/ualberta.ca/moodle-public/_/rsrc/1313598239676/home/moodle-icons/moodle75.png',
            'height' => '77',
            'width' => '75'
        ));

        $html = html_writer::start_tag('p');
        $html .= html_writer::tag('em', get_string('sub_heading', 'block_usingmoodle', $img));
        $html .= html_writer::end_tag('p');

        $html .= $this->createlistfromsetting(get_string('tip_sheets', 'block_usingmoodle'),
            get_config('block_usingmoodle', 'config_tip_sheets'));
        $html .= $this->createlistfromsetting(get_string('screencasts', 'block_usingmoodle'),
            get_config('block_usingmoodle', 'config_screencasts'));
        $html .= $this->createlistfromsetting(get_string('getting_help', 'block_usingmoodle'),
            get_config('block_usingmoodle', 'config_getting_help'));
        $html .= $this->createlistfromsetting(get_string('instructors', 'block_usingmoodle'),
            get_config('block_usingmoodle', 'config_for_instructors'));
        return html_writer::tag('div', $html, array( 'class' => 'no-overflow'));
    }

    private function createlistfromsetting($heading, $config) {
        $html = $this->createlisttitle( $heading );
        $items = array();
        $lines = explode("\n", $config);

        // Parse each line and split on | to get separate Name, URL, target.
        foreach ($lines as $line) {
            $line = trim($line);
            $bits = explode('|', $line, 3); // Name|URL|target.

            if (!array_key_exists(1, $bits) or empty($bits[0]) or empty($bits[1])) {
                // Every item must have a name and URL to be valid.
                continue;
            }

            // Make sure the url is a moodle url.
            $bits[1] = new moodle_url(trim($bits[1]));

            // Check to see if a target was specified.
            if (!array_key_exists(2, $bits) or empty($bits[2])) {
                // Set the target to _parent if there isn't one.
                $bits[2] = '_parent';
            }

            $items[$bits[0]] = array($bits[1], $bits[2]);
        }

        foreach ($items as $text => $link) {
            if (is_array($link)) {
                $html .= $this->createlistitem($text, $link[0], $link[1]);
            } else {
                $html .= $this->createlistitem($text, $link);
            }

        }
        return html_writer::tag('ul', $html);
    }

    private function createlisttitle( $text ) {
        return html_writer::tag('h6', $text );
    }

    private function createlistitem( $text, $link, $target = null ) {
        $link = urldecode($link);
        $html = html_writer::start_tag('a', array('href' => $link, 'target' => $target ? $target : '_parent'));
        $html .= html_writer::tag('span', $text, array( 'class' => 'instancename'));
        $html .= html_writer::end_tag('a');
        return html_writer::tag('li', $html);
    }
}