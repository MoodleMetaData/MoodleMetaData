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

defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;
require_once("{$CFG->dirroot}/config.php");
require_once("{$CFG->dirroot}/enrol/uaims/lib.php");
require_once("{$CFG->dirroot}/enrol/uaims/tests/eclass_course_manager_test.php");

/**
 * Tests for the enrol_uaims cron function.
 *
 * Test automatic opening and closing of courses based on dates
 * stored in eclass_course_management DB table.
 *
 * @package    enrol_uaims
 * @copyright  2014 Dom Royko royko@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class enrol_uaims_cron_test extends advanced_testcase {

    public function test_enrol_uaims_cron() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        // Run the cron on whatever data is in the database.
        // This checks that cron won't crash if the table is missing.
        $eup = new enrol_uaims_plugin();
        $eup->set_config('enableautocourseopenclose', 1);

        // Output buffering is disabled in phpunit runs.
        ini_set('output_buffering', 'On');

        ob_start();
        $eup->cron();
        ob_end_clean();
        ini_set('output_buffering', 'Off');

        $dbtables = $DB->get_tables(false);
        $this->assertEquals(true, array_key_exists('eclass_course_management', $dbtables));

        $ecmt = new eclass_course_manager_test;
        $eergisteren = time() - 2 * 86400;
        $gisteren = time() - 86400;
        $morgen = time() + 86400;
        $overmorgen = time() + 2 * 86400;

        // Course names are appended with Start, Now, and End, in chronological order.
        $coursense = $ecmt->create_course_vis_start_end(0, $morgen, $overmorgen);
        $coursesne = $ecmt->create_course_vis_start_end(0, $gisteren, $morgen);
        $coursesen = $ecmt->create_course_vis_start_end(0, $eergisteren, $gisteren);
        $coursenes = $ecmt->create_course_vis_start_end(0, $overmorgen, $morgen);
        $courseens = $ecmt->create_course_vis_start_end(1, $morgen, $gisteren);
        $courseesn = $ecmt->create_course_vis_start_end(0, $gisteren, $eergisteren);
        $courseopendone = $ecmt->create_course_vis_start_end(0, $gisteren, $morgen, time() - 86000);
        $courseclosedone = $ecmt->create_course_vis_start_end(1, $eergisteren, $gisteren, null, time() - 86000);

        // Disable auto open/close.  Visibility and times should stay unchanged.
        $eup->set_config('enableautocourseopenclose', 0);

        // Output buffering is disabled in phpunit runs.
        ini_set('output_buffering', 'On');

        ob_start();
        $eup->cron();
        ob_end_clean();
        ini_set('output_buffering', 'Off');

        $ecmt->check_courseid_vis_opened_closed($coursense->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($coursesne->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($coursesen->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($coursenes->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($courseens->id, 1, true, true);
        $ecmt->check_courseid_vis_opened_closed($courseesn->id, 0, true, true);

        $ecmt->check_courseid_vis_opened_closed($courseopendone->id, 0, false, true);
        $ecmt->check_courseid_vis_opened_closed($courseclosedone->id, 1, true, false);

        // Enable auto open/close.  Visibility and times should change selectively.
        $eup->set_config('enableautocourseopenclose', 1);

        // Output buffering is disabled in phpunit runs.
        ini_set('output_buffering', 'On');

        ob_start();
        $eup->cron();
        ob_end_clean();
        ini_set('output_buffering', 'Off');

        $ecmt->check_courseid_vis_opened_closed($coursense->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($coursesne->id, 1, false, true);
        $ecmt->check_courseid_vis_opened_closed($coursesen->id, 0, false, false);
        $ecmt->check_courseid_vis_opened_closed($coursenes->id, 0, true, true);
        $ecmt->check_courseid_vis_opened_closed($courseens->id, 0, true, false);
        $ecmt->check_courseid_vis_opened_closed($courseesn->id, 0, false, false);

        $ecmt->check_courseid_vis_opened_closed($courseopendone->id, 0, false, true);
        $ecmt->check_courseid_vis_opened_closed($courseclosedone->id, 1, false, false);
    }
}
