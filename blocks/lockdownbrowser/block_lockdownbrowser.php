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

class block_lockdownbrowser extends block_base {

    public function init() {
        $this->content_type = BLOCK_TYPE_TEXT;

        // Ensure title is unique even if string table is unavailable.
        $this->title = get_string("lockdownbrowser", "block_lockdownbrowser");
    }

    public function get_content() {
        global $CFG, $COURSE;
        if ($this->content != null) {

            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

        if (has_capability('moodle/course:manageactivities', context_course::instance($COURSE->id))) {
            $this->content->footer = '<a href="' . $CFG->wwwroot . '/blocks/lockdownbrowser/dashboard.php?course=' .
                $COURSE->id . '">' . get_string('dashboard', 'block_lockdownbrowser') . ' ...</a>';
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function applicable_formats() {
        return array(
            'site-index' => false,
            'course-view' => true,
            'course-view-social' => false,
            'mod' => false,
            'mod-quiz' => false
        );
    }

    public function has_config() {
        return true;
    }
}