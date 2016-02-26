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
 * is used by the generic sitepolicies block.
 *
 * @package sitepolicies
 * @author Asim Aziz
 **/

class block_sitepolicies_renderer extends plugin_renderer_base {

    public function render_block($config) {
        global $COURSE;
        $html = $this->createlisttitle( get_string('uofa_policies', 'block_sitepolicies') );

        $html .= $this->createlistfromsetting(get_config('block_sitepolicies', 'config_uofa_policies'));
        if ($COURSE->id > 1) {
            $html .= $this->createlistfromsettingcategory(get_config('block_sitepolicies', 'config_faculty_policies'),
                $COURSE->category);
        }
        // Course level links.
        if ( !empty($config->enablecourselinks) && $config->enablecourselinks == 1 && !empty($config->rawhtml['text']) ) {
            $clinks = '';
            $clinks .= html_writer::start_tag('div');
            if (!empty($config->title)) {
                $clinks .= $this->createlisttitle( $config->title );
            }
            $clinks .= $config->rawhtml['text'];
            $clinks .= html_writer::end_tag('div');
            $html .= $clinks;
        }
        return html_writer::tag('div', $html, array( 'class' => 'no-overflow'));

    }
    private function createlistfromsettingcategory($config, $category) {
        $html = '';
        $list = '';
        $items = array();
        $lines = explode("\n", $config);
        foreach ($lines as $line) {
            $bits = explode(':', $line, 2);
            if (!array_key_exists(1, $bits) or empty($bits[0]) or empty($bits[1]) or trim($bits[0]) != $category) {
                continue;
            } else {
                $html = $this->createlisttitle( trim($bits[1]) );
                break;
            }
        }

        if (!$html) {
            return '';
        }
        // Parse each line and split on | to get separate Name, URL, target.

        foreach ($lines as $line) {
            $line = trim($line);
            $bits = explode('|', $line, 4); // Category Id|Name|URL|target.

            if (!array_key_exists(1, $bits) or empty($bits[1]) or empty($bits[2]) or trim($bits[0]) != $category) {
                // Every item must have a name and URL to be valid.
                continue;
            }

            // Make sure the url is a moodle url.
            $bits[2] = new moodle_url(trim($bits[2]));

            // Check to see if a target was specified.
            if (!array_key_exists(3, $bits) or empty($bits[3])) {
                // Set the target to _parent if there isn't one.
                $bits[3] = '_parent';
            }

            $items[$bits[1]] = array($bits[2], $bits[3]);
        }

        foreach ($items as $text => $link) {
            if (is_array($link)) {
                $list .= $this->createlistitem($text, $link[0], $link[1]);
            } else {
                $list .= $this->createlistitem($text, $link);
            }

        }
        $html .= html_writer::tag('ul', $list);
        return $html;
    }

    private function createlistfromsetting($config) {
        $html = '';
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