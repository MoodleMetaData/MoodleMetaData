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
 * This is the locallib.php file for the project.  Any functions that are
 * used across several different modules are here.
 *
 * The functions here are actually included in the nursing navigation plugin
 * as well.  If you make any changes here, they probably should be made in
 * that plugin at the same time.
 *
 * @package    format_collblct
 * @category   course/format
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('FORMAT_CTWCL_LANG_TABLE', 'format_collblct');
define('DEFAULT_BACKGROUND', '#e7e7e7');
define('DEFAULT_FOREGROUND', '#2D2D2D');

/**
 * This method returns the number of active sections in a course or zero if the course does
 * not exist.
 *
 * @param int $courseid This is the course ID of the course to check.
 * @return int Total number of active sections.
 *
 */
function ctwcl_get_number_of_sections($courseid) {
    global $DB;

    $params = array($courseid, 'numsections');
    $query = "SELECT value FROM {course_format_options} WHERE courseid = ? AND name = ?";
    $records = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);

    if ($records != false) {
        return $records->value;
    } else {
        $params = array($courseid);
        $count = $DB->count_records('course_sections', array('course' => $courseid));
        return ($count != 0) ? $count : 0;
    }
}

/**
 * This method returns the section titles for all active sections within a course.  The data
 * is appened to the passed second parameter.
 *
 * @param int $courseid This is the course ID of the course to check.
 * @param array $sectionheaders The section headers will be appended here.
 * @return int Total number of active sections.
 *
 */
function ctwcl_get_section_titles($courseid, &$sectionheaders) {
    global $DB;

    $numberofsections = ctwcl_get_number_of_sections($courseid);

    $params = array($courseid);
    $query = "SELECT * FROM {course_sections} WHERE course = ? ORDER BY section";
    $sections = $DB->get_records_sql($query, $params);

    foreach ($sections as $section) {
        // Skip topic 0.
        if ($section->section != 0 && $section->section <= $numberofsections) {
            // If name is set, use that name, otherwise default to "Topic X".
            $sectionheaders[] = (isset($section->name)) ? $section->name : 'Topic '.$section->section;
        }
    }

    return $numberofsections;
}