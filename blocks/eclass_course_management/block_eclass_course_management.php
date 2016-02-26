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
 * Form for editing course_management block instances.
 *
 * @package   block_course_management
 * @copyright  Trevor Jones <tdjones@ualberta.ca>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("{$CFG->dirroot}/enrol/uaims/eclass_course_manager.php");

class block_eclass_course_management extends block_base
{

    public function init() {
        $this->title = get_string('pluginname', 'block_eclass_course_management');
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    public function specialization() {
        $this->title = format_string(get_string('block_title', 'block_eclass_course_management'));
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        global $COURSE;
        if ($this->content !== null) {
            return $this->content;
        }

        // Create empty content.
        $this->content = new stdClass;
        $this->content->text = '';
        // Use Strict as this block should only ever be in a course context.
        $context = context_course::instance($this->page->course->id);
        if (!has_capability('block/eclass_course_management:canseeblock', $context)) {
            return $this->content->text = '';
        }

        $open = EclassCourseManager::get_course_open($context->instanceid);
        $close = EclassCourseManager::get_course_close($context->instanceid);

        if ($open) {
            $date = usergetdate($open);
            $this->content->text .= '<br/>'. get_string('open_label', 'block_eclass_course_management') . ' ' .
                "{$date['month']} {$date['mday']}, {$date['year']}";
        } else {
            $this->content->text .= '<br/>'. get_string('nostartdate', 'block_eclass_course_management');
        }
        if ($close) {
            $date = usergetdate($close);
            $this->content->text .= '<br/>'. get_string('close_label', 'block_eclass_course_management') . ' ' .
                "{$date['month']} {$date['mday']}, {$date['year']}";
        } else {
            $this->content->text .= '<br/>'. get_string('noenddate', 'block_eclass_course_management');
        }

        if ($COURSE->visible) {
            $this->content->text .= '<br/>'. get_string('status_label', 'block_eclass_course_management') . ' ' .
                get_string('visible', 'block_eclass_course_management');
        } else {
            $this->content->text .= '<br/>'. get_string('status_label', 'block_eclass_course_management') . ' ' .
                get_string('notvisible', 'block_eclass_course_management');
        }

        if (!empty($this->content->text)) {
            $this->content->text = get_string('blockpreamble', 'block_eclass_course_management') . '<br/>' . $this->content->text;
        }
        if (has_capability('moodle/course:update', $context)) {
            $this->content->text .= "<br/><a href='/blocks/eclass_course_management/configure.php?course={$COURSE->id}'>Edit</a>";
        }
        return $this->content;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->content) && parent::instance_can_be_docked());
    }
}
