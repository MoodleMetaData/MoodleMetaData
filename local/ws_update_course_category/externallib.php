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
 * External Web Service for moving courses into new categories
 *
 * @package    local_ws_update_course_category
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

class local_ws_update_course_category_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_course_category_parameters() {
        return new external_function_parameters(
            array('courseid' => new external_value(PARAM_INT, 'The Moodle id of the course to update.'),
                   'categoryid' => new external_value(PARAM_INT, 'The Moodle id of the target category.'))
        );
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function update_course_category_returns() {
        return new external_value(PARAM_BOOL, 'True on success, false on error.');
    }

    /**
     * Moves a course into a category.
     * @param int $courseid the id of the course to move
     * @param int $categoryid the id of the target category to move the course to
     * @return bool success
     */
    public static function update_course_category($courseid, $categoryid) {
        global $USER;

        $params = self::validate_parameters(self::update_course_category_parameters(),
                                             array('courseid' => $courseid,
                                                    'categoryid' => $categoryid));

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('local/webservice:local_ws_update_course_category', $context)) {
            throw new moodle_exception(nocapabilitytousethisservice);
        }

        if ($courseid == 1) {
            echo "Cannot move course 1 (site).\n";
            throw new moodle_exception(cannotmovecourses);
        }

        echo "Moving course $courseid to category $categoryid.\n";

        return move_courses(array($courseid), $categoryid);
    }

}
