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

namespace block_eclass_course_management\event;
defined('MOODLE_INTERNAL') || die();

/**
 * @class course_close
 * @brief Triggered when course is closed.
 *
 * @package    block_eclass_course_management
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_closed extends \core\event\base {
    /**
     * Sets up basic info.
     */
    public function init() {
        $this->data['crud'] = 'u';
        // This is performed by someone who affects the learning of
        // students. Teacher or otherwise.
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'eclass_course_management';
    }

    public static function get_name() {
        return get_string('eventcourseclosed', 'block_eclass_course_management');
    }

    public function get_description() {
        global $DB;
        $coursemanagement = $DB->get_record($this->objecttable, array("id" => $this->objectid));
        $startdate = date("D M j Y", $coursemanagement->startdate);
        $enddate = date("D M j Y", $coursemanagement->enddate);
        return
            "User with user id: {$this->userid} closed course by setting start date to: {$startdate} and ".
            "end date to: {$enddate}.";
    }
}